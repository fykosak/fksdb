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
use FKSDB\Results\SQLResultsCache;
use FKSDB\Submits\SeriesTable;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Tracy\Debugger;
use Nette\InvalidArgumentException;

/**
 * Class PointsPresenter
 * @package OrgModule
 */
class PointsPresenter extends SeriesPresenter {

    /**
     * Show all tasks?
     * @persistent
     */
    public $all;

    /**
     * @var SQLResultsCache
     */
    private $SQLResultsCache;

    /**
     * @var SeriesTable
     */
    private $seriesTable;

    /**
     * @var ServiceTask
     */
    private $serviceTask;

    /**
     * @var ServiceTaskContribution
     */
    private $serviceTaskContribution;

    /**
     * @param SQLResultsCache $SQLResultsCache
     */
    public function injectSQLResultsCache(SQLResultsCache $SQLResultsCache) {
        $this->SQLResultsCache = $SQLResultsCache;
    }

    /**
     * @param SeriesTable $seriesTable
     */
    public function injectSeriesTable(SeriesTable $seriesTable) {
        $this->seriesTable = $seriesTable;
    }

    /**
     * @param ServiceTask $serviceTask
     */
    public function injectServiceTask(ServiceTask $serviceTask) {
        $this->serviceTask = $serviceTask;
    }

    /**
     * @param ServiceTaskContribution $serviceTaskContribution
     */
    public function injectServiceTaskContribution(ServiceTaskContribution $serviceTaskContribution) {
        $this->serviceTaskContribution = $serviceTaskContribution;
    }

    protected function startup() {
        parent::startup();
        $this->seriesTable->setContest($this->getSelectedContest());
        $this->seriesTable->setYear($this->getSelectedYear());
        $this->seriesTable->setSeries($this->getSelectedSeries());
    }

    /**
     * @throws BadRequestException
     */
    public function titleEntry() {
        $this->setTitle(sprintf(_('Zadávání bodů %d. série'), $this->getSelectedSeries()), 'fa fa-trophy');
    }

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
        if ($this->all) {
            $this->seriesTable->setTaskFilter(null);
        } else {
            $gradedTasks = $this->getGradedTasks();
            $this->seriesTable->setTaskFilter($gradedTasks);
        }
    }


    public function renderEntry() {
        $this->template->showAll = (bool)$this->all;
    }

    /**
     * @return PointsFormControl
     */
    protected function createComponentPointsForm(): PointsFormControl {
        return new PointsFormControl(function () {
            $this->SQLResultsCache->recalculate($this->getSelectedContest(), $this->getSelectedYear());
        }, $this->getContext(), $this->seriesTable);
    }

    /**
     * @return PointsPreviewControl
     */
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

    /**
     * @return array
     */
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
                'type' => ModelTaskContribution::TYPE_GRADE
            ])->fetchPairs('task_id', 'task_id');
        return array_values($gradedTasks);
    }

    /**
     * @return string
     */
    protected function getContainerClassNames(): string {
        return str_replace('container ', 'container-fluid ', parent::getContainerClassNames());
    }
}
