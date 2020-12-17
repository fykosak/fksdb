<?php

namespace FKSDB\Modules\OrgModule;

use Exception;
use FKSDB\Components\Controls\Inbox\PointPreview\PointsPreviewControl;
use FKSDB\Components\Controls\Inbox\PointsForm\PointsFormControl;
use FKSDB\Modules\Core\PresenterTraits\ISeriesPresenter;
use FKSDB\Model\ORM\Models\ModelContest;
use FKSDB\Model\UI\PageTitle;
use FKSDB\Model\ORM\Models\ModelLogin;
use FKSDB\Model\ORM\Models\ModelTask;
use FKSDB\Model\ORM\Models\ModelTaskContribution;
use FKSDB\Model\ORM\Services\ServiceTask;
use FKSDB\Model\ORM\Services\ServiceTaskContribution;
use FKSDB\Model\Results\SQLResultsCache;
use FKSDB\Model\Submits\SeriesTable;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Tracy\Debugger;
use Nette\InvalidArgumentException;

/**
 * Class PointsPresenter
 *
 */
class PointsPresenter extends BasePresenter implements ISeriesPresenter {
    /**
     * Show all tasks?
     * @persistent
     */
    public $all;

    private SQLResultsCache $SQLResultsCache;
    private SeriesTable $seriesTable;
    private ServiceTask $serviceTask;
    private ServiceTaskContribution $serviceTaskContribution;

    final public function injectQuarterly(
        SQLResultsCache $SQLResultsCache,
        SeriesTable $seriesTable,
        ServiceTask $serviceTask,
        ServiceTaskContribution $serviceTaskContribution
    ): void {
        $this->SQLResultsCache = $SQLResultsCache;
        $this->seriesTable = $seriesTable;
        $this->serviceTask = $serviceTask;
        $this->serviceTaskContribution = $serviceTaskContribution;
    }

    protected function startup(): void {
        parent::startup();
        $this->seriesTable->setContest($this->getSelectedContest());
        $this->seriesTable->setYear($this->getSelectedYear());
        $this->seriesTable->setSeries($this->getSelectedSeries());
    }

    /**
     * @return void
     * @throws AbortException
     */
    public function titleEntry(): void {
        $this->setPageTitle(new PageTitle(sprintf(_('Grade series %d'), $this->getSelectedSeries()), 'fa fa-trophy'));
    }

    /**
     * @return void
     * @throws AbortException
     */
    public function titlePreview(): void {
        $this->setPageTitle(new PageTitle(_('Points list'), 'fa fa-inbox'));
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

    public function renderEntry(): void {
        $this->template->showAll = (bool)$this->all;
        if ($this->getSelectedContest()->contest_id === ModelContest::ID_VYFUK && $this->getSelectedSeries() > 6) {
            $this->template->hasQuizTask = true;
        } else {
            $this->template->hasQuizTask = false;
        }
    }

    protected function createComponentPointsForm(): PointsFormControl {
        return new PointsFormControl(function () {
            $this->SQLResultsCache->recalculate($this->getSelectedContest(), $this->getSelectedYear());
        }, $this->getContext(), $this->seriesTable);
    }

    protected function createComponentPointsTableControl(): PointsPreviewControl {
        return new PointsPreviewControl($this->getContext(), $this->seriesTable);
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

            $years = $this->serviceTask->getTable()
                ->select('year')
                ->where([
                    'contest_id' => $contest->contest_id,
                ])->group('year');

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
        try {
            $contest = $this->getSelectedContest();
            $year = $this->getSelectedYear();
            $series = $this->getSelectedSeries();

            $this->SQLResultsCache->calculateQuizPoints($contest, $year, $series);
            $this->flashMessage(_('Body kvízových úloh spočteny.'), self::FLASH_INFO);
        } catch (Exception $exception) {
            $this->flashMessage(_('Chyba při výpočtu.'), self::FLASH_ERROR);
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

    /**
     * @param PageTitle $pageTitle
     * @return void
     * @throws AbortException
     */
    protected function setPageTitle(PageTitle $pageTitle): void {
        $pageTitle->subTitle .= ' ' . sprintf(_('%d. series'), $this->getSelectedSeries());
        parent::setPageTitle($pageTitle);
    }
}
