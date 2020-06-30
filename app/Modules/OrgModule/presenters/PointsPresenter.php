<?php

namespace FKSDB\Modules\OrgModule;

use Exception;
use FKSDB\Components\Controls\Inbox\PointsFormControl;
use FKSDB\Components\Controls\Inbox\PointsPreviewControl;
use FKSDB\Modules\Core\PresenterTraits\ISeriesPresenter;
use FKSDB\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use FKSDB\Modules\Core\PresenterTraits\{SeriesPresenterTrait};
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
 *
 */
class PointsPresenter extends BasePresenter implements ISeriesPresenter {

    use SeriesPresenterTrait;

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
     * @return void
     */
    public function injectSQLResultsCache(SQLResultsCache $SQLResultsCache) {
        $this->SQLResultsCache = $SQLResultsCache;
    }

    /**
     * @param SeriesTable $seriesTable
     * @return void
     */
    public function injectSeriesTable(SeriesTable $seriesTable) {
        $this->seriesTable = $seriesTable;
    }

    /**
     * @param ServiceTask $serviceTask
     * @return void
     */
    public function injectServiceTask(ServiceTask $serviceTask) {
        $this->serviceTask = $serviceTask;
    }

    /**
     * @param ServiceTaskContribution $serviceTaskContribution
     * @return void
     */
    public function injectServiceTaskContribution(ServiceTaskContribution $serviceTaskContribution) {
        $this->serviceTaskContribution = $serviceTaskContribution;
    }

    protected function startup() {
        $this->seriesTraitStartup();
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
        $this->setPageTitle(new PageTitle(sprintf(_('Zadávání bodů %d. série'), $this->getSelectedSeries()), 'fa fa-trophy'));
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titlePreview() {
        $this->setPageTitle(new PageTitle(_('Points'), 'fa fa-inbox'));
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function authorizedEntry() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('submit', 'edit', $this->getSelectedContest()));
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function authorizedPreview() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('submit', 'points', $this->getSelectedContest()));
    }

    /**
     * @return void
     */
    public function actionEntry() {
        $this->seriesTable->setTaskFilter($this->all ? null : $this->getGradedTasks());
    }

    /**
     * @return void
     */
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
     * @return void
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
     * @return void
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

    protected function beforeRender() {
        $this->getPageStyleContainer()->mainContainerClassName = str_replace('container ', 'container-fluid ', $this->getPageStyleContainer()->mainContainerClassName) . ' px-3';
        parent::beforeRender();
    }

    /**
     * @param PageTitle $pageTitle
     * @return void
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    protected function setPageTitle(PageTitle $pageTitle) {
        $pageTitle->subTitle .= ' ' . sprintf(_('%d. series'), $this->getSelectedSeries());
        parent::setPageTitle($pageTitle);
    }
}
