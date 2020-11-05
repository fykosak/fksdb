<?php

namespace FKSDB\Modules\OrgModule;

use Exception;
use FKSDB\Components\Controls\Inbox\PointsFormControl;
use FKSDB\Components\Controls\Inbox\PointsPreviewControl;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Modules\Core\PresenterTraits\ISeriesPresenter;
use FKSDB\ORM\Models\ModelContest;
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
        $this->seriesTraitStartup();
        parent::startup();
        $this->seriesTable->setContest($this->getSelectedContest());
        $this->seriesTable->setYear($this->getSelectedYear());
        $this->seriesTable->setSeries($this->getSelectedSeries());
    }

    /**
     * @return void
     * @throws ForbiddenRequestException
     * @throws BadTypeException
     */
    public function titleEntry(): void {
        $this->setPageTitle(new PageTitle(sprintf(_('Grade series %d'), $this->getSelectedSeries()), 'fa fa-trophy'));
    }

    /**
     * @return void
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     */
    public function titlePreview(): void {
        $this->setPageTitle(new PageTitle(_('Points'), 'fa fa-inbox'));
    }

    /**
     * @return void
     * @throws ForbiddenRequestException
     * @throws BadTypeException
     */
    public function authorizedEntry(): void {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('submit', 'edit', $this->getSelectedContest()));
    }

    /**
     * @return void
     * @throws ForbiddenRequestException
     * @throws BadTypeException
     */
    public function authorizedPreview(): void {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('submit', 'points', $this->getSelectedContest()));
    }

    public function actionEntry(): void {
        $this->seriesTable->setTaskFilter($this->all ? null : $this->getGradedTasks());
    }

    /**
     * @return void
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     */
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
     * @throws ForbiddenRequestException
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
     * @throws ForbiddenRequestException
     * @throws BadTypeException
     */
    protected function setPageTitle(PageTitle $pageTitle): void {
        $pageTitle->subTitle .= ' ' . sprintf(_('%d. series'), $this->getSelectedSeries());
        parent::setPageTitle($pageTitle);
    }
}
