<?php

namespace FKSDB\Modules\OrgModule;

use Exception;
use FKSDB\Components\Controls\Inbox\PointPreview\PointsPreviewComponent;
use FKSDB\Components\Controls\Inbox\PointsForm\PointsFormComponent;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\UI\PageTitle;
use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\ORM\Models\ModelTask;
use FKSDB\Models\ORM\Models\ModelTaskContribution;
use FKSDB\Models\ORM\Services\ServiceTaskContribution;
use FKSDB\Models\Results\SQLResultsCache;
use FKSDB\Models\Submits\SeriesTable;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;
use Tracy\Debugger;
use Nette\InvalidArgumentException;

class PointsPresenter extends BasePresenter {

    /**
     * Show all tasks?
     * @persistent
     */
    public $all;
    private SQLResultsCache $SQLResultsCache;
    private SeriesTable $seriesTable;
    private ServiceTaskContribution $serviceTaskContribution;

    final public function injectQuarterly(
        SQLResultsCache $SQLResultsCache,
        SeriesTable $seriesTable,
        ServiceTaskContribution $serviceTaskContribution
    ): void {
        $this->SQLResultsCache = $SQLResultsCache;
        $this->seriesTable = $seriesTable;
        $this->serviceTaskContribution = $serviceTaskContribution;
    }

    protected function startup(): void {
        parent::startup();
        $this->seriesTable->setContest($this->getSelectedContest());
        $this->seriesTable->setYear($this->getSelectedYear());
        $this->seriesTable->setSeries($this->getSelectedSeries());
    }

    public function titleEntry(): void {
        $this->setPageTitle(new PageTitle(sprintf(_('Grade series %d'), $this->getSelectedSeries()), 'fas fa-trophy'));
    }

    public function titlePreview(): void {
        $this->setPageTitle(new PageTitle(_('Points list'), 'fas fa-inbox'));
    }

    public function authorizedEntry(): void {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('submit', 'edit', $this->getSelectedContest()));
    }

    public function authorizedPreview(): void {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('submit', 'points', $this->getSelectedContest()));
    }

    public function actionEntry(): void {
        $this->seriesTable->setTaskFilter($this->all ? null : $this->getGradedTasks());
    }

    final public function renderEntry(): void {
        $this->template->showAll = (bool)$this->all;
        if ($this->getSelectedContest()->contest_id === ModelContest::ID_VYFUK && $this->getSelectedSeries() > 6) {
            $this->template->hasQuizTask = true;
        } else {
            $this->template->hasQuizTask = false;
        }
    }

    protected function createComponentPointsForm(): PointsFormComponent {
        return new PointsFormComponent(function () {
            $this->SQLResultsCache->recalculate($this->getSelectedContest(), $this->getSelectedYear());
        }, $this->getContext(), $this->seriesTable);
    }

    protected function createComponentPointsTableControl(): PointsPreviewComponent {
        return new PointsPreviewComponent($this->getContext(), $this->seriesTable);
    }

    /**
     * @return void
     * @throws AbortException
     */
    public function handleInvalidate(): void {
        try {
            $this->SQLResultsCache->invalidate($this->getSelectedContest(), $this->getSelectedYear());
            $this->flashMessage(_('Points invalidated.'), self::FLASH_INFO);
        } catch (Exception $exception) {
            $this->flashMessage(_('Error during invalidation.'), self::FLASH_ERROR);
            Debugger::log($exception);
        }

        $this->redirect('this');
    }

    /**
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     */
    public function handleRecalculateAll(): void {
        try {
            $contest = $this->getSelectedContest();

            $years = $contest->related(DbNames::TAB_TASK)
                ->select('year')
                ->group('year');
            /** @var ModelTask|ActiveRow $year */
            foreach ($years as $year) {
                $this->SQLResultsCache->recalculate($contest, $year->year);
            }

            $this->flashMessage(_('Points recounted.'), self::FLASH_INFO);
        } catch (InvalidArgumentException $exception) {
            $this->flashMessage(_('Error while recounting.'), self::FLASH_ERROR);
            Debugger::log($exception);
        }

        $this->redirect('this');
    }

    /**
     * @return void
     * @throws AbortException
     */
    public function handleCalculateQuizPoints(): void {
        $contest = $this->getSelectedContest();
        $year = $this->getSelectedYear();
        $series = $this->getSelectedSeries();

        $this->SQLResultsCache->calculateQuizPoints($contest, $year, $series);
        $this->flashMessage(_('Points recounted.'), self::FLASH_INFO);
        try {
        } catch (Exception $exception) {
            $this->flashMessage(_('Error during calculation.'), self::FLASH_ERROR);
            Debugger::log($exception);
        }
    }

    private function getGradedTasks(): array {
        /**@var ModelLogin $login */
        $login = $this->getUser()->getIdentity();
        $person = $login->getPerson();
        if (!$person) {
            return [];
        }

        $taskIds = [];
        /** @var ModelTask $task */
        foreach ($this->seriesTable->getTasks() as $task) {
            $taskIds[] = $task->task_id;
        }
        $gradedTasks = $this->serviceTaskContribution->getTable()
            ->where([
                'person_id' => $person->person_id,
                'task_id' => $taskIds,
                'type' => ModelTaskContribution::TYPE_GRADE,
            ])->fetchPairs('task_id', 'task_id');
        return array_values($gradedTasks);
    }

    protected function beforeRender(): void {
        $this->getPageStyleContainer()->setWidePage();
        parent::beforeRender();
    }
}
