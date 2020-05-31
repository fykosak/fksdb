<?php

namespace OrgModule;

use Exception;
use FKSDB\Components\Controls\Inbox\PointsFormControl;
use FKSDB\Components\Controls\Inbox\PointsPreviewControl;
use FKSDB\ORM\Models\ModelLogin;
use FKSDB\ORM\Models\ModelTask;
use FKSDB\ORM\Models\ModelTaskContribution;
use FKSDB\ORM\Services\ServiceTask;
use FKSDB\ORM\Services\ServiceTaskContribution;
use FKSDB\UI\PageStyleContainer;
use FKSDB\Results\SQLResultsCache;
use FKSDB\Submits\SeriesTable;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Tracy\Debugger;
use Nette\InvalidArgumentException;

/**
 * Class PointsPresenter
 * *
 */
class PointsPresenter extends SeriesPresenter {

    /**
     * Show all tasks?
     * @persistent
     */
    public $all;

    private SQLResultsCache $SQLResultsCache;

    private SeriesTable $seriesTable;

    private ServiceTask $serviceTask;

    private ServiceTaskContribution $serviceTaskContribution;

    public function injectSQLResultsCache(SQLResultsCache $SQLResultsCache): void {
        $this->SQLResultsCache = $SQLResultsCache;
    }

    public function injectSeriesTable(SeriesTable $seriesTable): void {
        $this->seriesTable = $seriesTable;
    }

    public function injectServiceTask(ServiceTask $serviceTask): void {
        $this->serviceTask = $serviceTask;
    }

    public function injectServiceTaskContribution(ServiceTaskContribution $serviceTaskContribution): void {
        $this->serviceTaskContribution = $serviceTaskContribution;
    }

    protected function startup() {
        parent::startup();
        $this->seriesTable->setContest($this->getSelectedContest());
        $this->seriesTable->setYear($this->getSelectedYear());
        $this->seriesTable->setSeries($this->getSelectedSeries());
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleEntry() {
        $this->setTitle(sprintf(_('Zadávání bodů %d. série'), $this->getSelectedSeries()), 'fa fa-trophy');
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titlePreview() {
        $this->setTitle(_('Points'), 'fa fa-inbox');
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedEntry() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('submit', 'edit', $this->getSelectedContest()));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedPreview() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('submit', 'points', $this->getSelectedContest()));
    }

    public function actionEntry() {
        $this->seriesTable->setTaskFilter($this->all ? null : $this->getGradedTasks());
    }


    public function renderEntry() {
        $this->template->showAll = (bool)$this->all;
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
     * @throws AbortException
     */
    public function handleInvalidate() {
        try {
            $this->SQLResultsCache->invalidate($this->getSelectedContest(), $this->getSelectedYear());
            $this->flashMessage(_('Body invalidovány.'), self::FLASH_INFO);
        } catch (Exception $exception) {
            $this->flashMessage(_('Chyba při invalidaci.'), self::FLASH_ERROR);
            Debugger::log($exception);
        }

        $this->redirect('this');
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function handleRecalculateAll() {
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

            $this->flashMessage(_('Body přepočítány.'), self::FLASH_INFO);
        } catch (InvalidArgumentException $exception) {
            $this->flashMessage(_('Chyba při přepočtu.'), self::FLASH_ERROR);
            Debugger::log($exception);
        }

        $this->redirect('this');
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

    protected function getPageStyleContainer(): PageStyleContainer {
        $container = parent::getPageStyleContainer();
        $container->mainContainerClassName = str_replace('container ', 'container-fluid ', $container->mainContainerClassName) . ' px-3';
        return $container;
    }
}
