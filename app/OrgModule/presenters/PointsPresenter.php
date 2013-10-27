<?php

namespace OrgModule;

use Exception;
use FKSDB\Components\Forms\Controls\ContestantSubmits;
use FKSDB\Components\Forms\OptimisticForm;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use ModelContest;
use ModelTaskContribution;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use ServiceSubmit;
use ServiceTask;
use ServiceTaskContribution;
use SQLResultsCache;
use Submits\SeriesTable;

class PointsPresenter extends SeriesPresenter {

    /**
     * Show all tasks?
     * 
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
     * @var ServiceSubmit
     */
    private $serviceSubmit;

    /**
     * @var ServiceTask
     */
    private $serviceTask;

    /**
     * @var ServiceTaskContribution
     */
    private $serviceTaskContribution;

    public function injectSQLResultsCache(SQLResultsCache $SQLResultsCache) {
        $this->SQLResultsCache = $SQLResultsCache;
    }

    public function injectSeriesTable(SeriesTable $seriesTable) {
        $this->seriesTable = $seriesTable;
    }

    public function injectServiceSubmit(ServiceSubmit $serviceSubmit) {
        $this->serviceSubmit = $serviceSubmit;
    }

    public function injectServiceTask(ServiceTask $serviceTask) {
        $this->serviceTask = $serviceTask;
    }

    public function injectServiceTaskContribution(ServiceTaskContribution $serviceTaskContribution) {
        $this->serviceTaskContribution = $serviceTaskContribution;
    }

    protected function startup() {
        parent::startup();
        $this->seriesTable->setContest($this->getSelectedContest());
        $this->seriesTable->setYear($this->getSelectedYear());
        $this->seriesTable->setSeries($this->getSelectedSeries());
    }

    public function actionDefault() {
        if (!$this->getContestAuthorizator()->isAllowed('submit', 'edit', $this->getSelectedContest())) {
            throw new BadRequestException('Nedostatečné oprávnění.', 403);
        }
        if ($this->all) {
            $this->seriesTable->setTaskFilter(null);
        } else {
            $gradedTasks = $this->getGradedTasks();
            $this->seriesTable->setTaskFilter($gradedTasks);
        }
    }

    public function renderDefault() {
        $this['pointsForm']->setDefaults();
        $this->template->showAll = (bool) $this->all;
    }

    protected function createComponentPointsForm($name) {
        $form = new OptimisticForm(
                array($this->seriesTable, 'getFingerprint'), array($this->seriesTable, 'formatAsFormValues')
        );
        $form->setRenderer(new BootstrapRenderer());

        $contestants = $this->seriesTable->getContestants();
        $tasks = $this->seriesTable->getTasks();
        $gradedTasks = $this->getGradedTasks();

        $container = $form->addContainer(SeriesTable::FORM_CONTESTANT);

        foreach ($contestants as $contestant) {
            $control = new ContestantSubmits($tasks, $contestant, $this->serviceSubmit, $contestant->getPerson()->getFullname());
            $control->setClassName('points');

            $namingContainer = $container->addContainer($contestant->ct_id);
            $namingContainer->addComponent($control, SeriesTable::FORM_SUBMIT);
        }

        $form->addSubmit('save', 'Uložit');
        $form->onSuccess[] = array($this, 'pointsFormSuccess');

        // JS dependencies        
        $this->registerJSFile('js/points.js');

        return $form;
    }

    public function pointsFormSuccess(Form $form) {
        $values = $form->getValues();

        try {
            $this->serviceSubmit->getConnection()->beginTransaction();

            foreach ($values[SeriesTable::FORM_CONTESTANT] as $container) {
                $submits = $container[SeriesTable::FORM_SUBMIT];
                foreach ($submits as $submit) {
                    if (!$submit->isEmpty()) {
                        $this->serviceSubmit->save($submit);
                    }
                }
            }
            $this->serviceSubmit->getConnection()->commit();


            // recalculate points (separate transaction)
            $this->SQLResultsCache->recalculate($this->getSelectedContest(), $this->getSelectedYear());


            $this->flashMessage('Body úloh uloženy.', self::FLASH_SUCCESS);
        } catch (Exception $e) {
            $this->flashMessage('Chyba při ukládání bodů.', self::FLASH_ERROR);
            Debugger::log($e);
        }
        $this->redirect('this');
    }

    public function handleInvalidate() {
        try {
            $this->SQLResultsCache->invalidate($this->getSelectedContest(), $this->getSelectedYear());
            $this->flashMessage('Body invalidovány.', self::FLASH_INFO);
        } catch (Exception $e) {
            $this->flashMessage('Chyba při invalidaci.', self::FLASH_ERROR);
            Debugger::log($e);
        }

        $this->redirect('this');
    }

    public function handleRecalculateAll() {
        try {
            foreach ($this->getAvailableContests() as $contest) {
                $contest = ModelContest::createFromTableRow($contest);

                $years = $this->serviceTask->getTable()
                                ->select('year')
                                ->where(array(
                                    'contest_id' => $contest->contest_id,
                                ))->group('year');

                foreach ($years as $year) {
                    $this->SQLResultsCache->recalculate($contest, $year->year);
                }
            }

            $this->flashMessage('Body přepočítány.', self::FLASH_INFO);
        } catch (Exception $e) {
            $this->flashMessage('Chyba při přepočtu.', self::FLASH_ERROR);
            Debugger::log($e);
        }

        $this->redirect('this');
    }

    private function getGradedTasks() {
        $login = $this->getUser()->getIdentity();
        $orgIds = array();
        foreach ($login->getActiveOrgs($this->yearCalculator) as $contestId => $orgId) {
            if ($orgId) {
                $orgIds[] = $orgId;
            }
        }
        $taskIds = array();
        foreach ($this->seriesTable->getTasks() as $task) {
            $taskIds[] = $task->task_id;
        }
        $gradedTasks = $this->serviceTaskContribution->getTable()
                        ->where(array(
                            'org_id' => $orgIds,
                            'task_id' => $taskIds,
                            'type' => ModelTaskContribution::TYPE_GRADE
                        ))->fetchPairs('task_id', 'task_id');
        return array_values($gradedTasks);
    }

}
