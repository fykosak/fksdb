<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\Inbox\PointPreview\PointsPreviewComponent;
use FKSDB\Components\Controls\Inbox\PointsForm\PointsFormComponent;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\ORM\Models\ModelTask;
use FKSDB\Models\ORM\Models\ModelTaskContribution;
use FKSDB\Models\ORM\Services\ServiceTaskContribution;
use FKSDB\Models\Results\SQLResultsCache;
use FKSDB\Models\Submits\SeriesTable;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;
use Nette\InvalidArgumentException;
use Tracy\Debugger;

class PointsPresenter extends BasePresenter
{

    /**
     * Show all tasks?
     * @persistent
     */
    public ?bool $all = null;
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

    public function titleEntry(): PageTitle
    {
        return new PageTitle(sprintf(_('Grade series %d'), $this->getSelectedSeries()), 'fas fa-pen');
    }

    public function titlePreview(): PageTitle
    {
        return new PageTitle(_('Points list'), 'fas fa-clipboard-list');
    }

    public function authorizedEntry(): void
    {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('submit', 'edit', $this->getSelectedContest()));
    }

    public function authorizedPreview(): void
    {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('submit', 'points', $this->getSelectedContest()));
    }

    public function actionEntry(): void
    {
        $this->seriesTable->setTaskFilter($this->all ? null : $this->getGradedTasks());
    }

    private function getGradedTasks(): array
    {
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
            ->where(
                [
                    'person_id' => $person->person_id,
                    'task_id' => $taskIds,
                    'type' => ModelTaskContribution::TYPE_GRADE,
                ]
            )->fetchPairs('task_id', 'task_id');
        return array_values($gradedTasks);
    }

    final public function renderEntry(): void
    {
        $this->template->showAll = (bool)$this->all;
        if ($this->getSelectedContest()->contest_id === ModelContest::ID_VYFUK && $this->getSelectedSeries() > 6) {
            $this->template->hasQuizTask = true;
        } else {
            $this->template->hasQuizTask = false;
        }
    }

    public function handleInvalidate(): void
    {
        try {
            $this->SQLResultsCache->invalidate($this->getSelectedContestYear());
            $this->flashMessage(_('Points invalidated.'), self::FLASH_INFO);
        } catch (\Exception $exception) {
            $this->flashMessage(_('Error during invalidation.'), self::FLASH_ERROR);
            Debugger::log($exception);
        }

        $this->redirect('this');
    }

    /**
     * @throws BadRequestException
     */
    public function handleRecalculateAll(): void
    {
        try {
            $years = $this->getSelectedContestYear()->getContest()->related(DbNames::TAB_TASK)
                ->select('year')
                ->group('year');
            /** @var ModelTask|ActiveRow $year */
            foreach ($years as $year) {
                $this->SQLResultsCache->recalculate($this->getSelectedContest()->getContestYear($year->year));
            }

            $this->flashMessage(_('Points recounted.'), self::FLASH_INFO);
        } catch (InvalidArgumentException $exception) {
            $this->flashMessage(_('Error while recounting.'), self::FLASH_ERROR);
            Debugger::log($exception);
        }

        $this->redirect('this');
    }

    public function handleCalculateQuizPoints(): void
    {
        try {
            $this->SQLResultsCache->calculateQuizPoints($this->getSelectedContestYear(), $this->getSelectedSeries());
            $this->flashMessage(_('Calculate quiz points.'), self::FLASH_INFO);
        } catch (\Exception $exception) {
            $this->flashMessage(_('Error during calculation.'), self::FLASH_ERROR);
            Debugger::log($exception);
        }
    }

    protected function startup(): void
    {
        parent::startup();
        $this->seriesTable->setContestYear($this->getSelectedContestYear());
        $this->seriesTable->setSeries($this->getSelectedSeries());
    }

    protected function createComponentPointsForm(): PointsFormComponent
    {
        return new PointsFormComponent(
            fn() => $this->SQLResultsCache->recalculate($this->getSelectedContestYear()),
            $this->getContext(),
            $this->seriesTable,
        );
    }

    protected function createComponentPointsTableControl(): PointsPreviewComponent
    {
        return new PointsPreviewComponent($this->getContext(), $this->seriesTable);
    }

    protected function beforeRender(): void
    {
        $this->getPageStyleContainer()->setWidePage();
        parent::beforeRender();
    }
}
