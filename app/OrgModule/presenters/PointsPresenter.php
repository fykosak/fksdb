<?php

namespace OrgModule;

use Exception;
use FKSDB\Components\Forms\Controls\ContestantSubmits;
use FKSDB\Components\Forms\OptimisticForm;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use ModelTaskContribution;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use Nette\InvalidArgumentException;
use Nette\Utils\Html;
use ServiceSubmit;
use ServiceTask;
use ServiceTaskContribution;
use ServiceTaskStudyYear;
use SQLResultsCache;
use Submits\SeriesTable;

class PointsPresenter extends BasePresenter {

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

    /**
     * @var ServiceTaskStudyYear
     */
    private $serviceTaskStudyYear;

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

    public function injectServiceTaskStudyYear(ServiceTaskStudyYear $serviceTaskStudyYear) {
        $this->serviceTaskStudyYear = $serviceTaskStudyYear;
    }

    protected function startup() {
        parent::startup();
        $this->seriesTable->setContest($this->getSelectedContest());
        $this->seriesTable->setYear($this->getSelectedYear());
        $this->seriesTable->setSeries($this->getSelectedSeries());
    }

    public function authorizedDefault() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('submit', 'edit', $this->getSelectedContest()));
    }

    public function actionDefault() {
        if ($this->all) {
            $this->seriesTable->setTaskFilter(null);
        } else {
            $gradedTasks = $this->getGradedTasks();
            $this->seriesTable->setTaskFilter($gradedTasks);
        }
    }

    public function titleDefault() {
        $this->setTitle(sprintf(_('Zadávání bodů %d. série'), $this->getSelectedSeries()));
    }

    public function renderDefault() {
        $this['pointsForm']->setDefaults();
        $this->template->showAll = (bool) $this->all;
    }

    protected function createComponentPointsForm($name) {
        $form = new OptimisticForm(
                array($this->seriesTable, 'getFingerprint'), array($this->seriesTable, 'formatAsFormValues')
        );
        $renderer = new BootstrapRenderer();
        $renderer->setColLeft(2);
        $renderer->setColRight(10);
        $form->setRenderer($renderer);


        $contestants = $this->seriesTable->getContestants();
        $tasks = $this->seriesTable->getTasks();
        $gradedTasks = $this->getGradedTasks();

        $container = $form->addContainer(SeriesTable::FORM_CONTESTANT);

        foreach ($contestants as $contestant) {
            $fullname = $contestant->getPerson()->getFullname();
            $schoolAbbrev = $contestant->getPerson()->getHistory($this->getSelectedAcademicYear())->getSchool()->name_abbrev;
            $schoolLabel = Html::el('small');
            $schoolLabel->setText('('.$schoolAbbrev.')');
            $schoolLabel->class = 'text-muted';
            $label = Html::el()
                    ->setText($fullname)
                    ->add(Html::el('br'))
                    ->add($schoolLabel);
            $control = new ContestantSubmits($tasks, $contestant, $this->serviceSubmit, $this->serviceTaskStudyYear, $this->getSelectedAcademicYear(), $label);
            $control->setClassName('points');

            $namingContainer = $container->addContainer($contestant->ct_id);
            $namingContainer->addComponent($control, SeriesTable::FORM_SUBMIT);
        }

        $submit = $form->addSubmit('save', _('Uložit'));
        $submit->getControlPrototype()->addClass('btn-success');
        $form->onSuccess[] = [$this, 'pointsFormSuccess'];

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


            $this->flashMessage(_('Body úloh uloženy.'), self::FLASH_SUCCESS);
        } catch (Exception $e) {
            $this->flashMessage(_('Chyba při ukládání bodů.'), self::FLASH_ERROR);
            Debugger::log($e);
        }
        $this->redirect('this');
    }

    public function handleInvalidate() {
        try {
            $this->SQLResultsCache->invalidate($this->getSelectedContest(), $this->getSelectedYear());
            $this->flashMessage(_('Body invalidovány.'), self::FLASH_INFO);
        } catch (Exception $e) {
            $this->flashMessage(_('Chyba při invalidaci.'), self::FLASH_ERROR);
            Debugger::log($e);
        }

        $this->redirect('this');
    }

    public function handleRecalculateAll() {
        try {

            $contest = $this->getSelectedContest();

            $years = $this->serviceTask->getTable()
                            ->select('year')
                            ->where(array(
                                'contest_id' => $contest->contest_id,
                            ))->group('year');

            foreach ($years as $year) {
                $this->SQLResultsCache->recalculate($contest, $year->year);
            }


            $this->flashMessage(_('Body přepočítány.'), self::FLASH_INFO);
        } catch (InvalidArgumentException $e) {
            $this->flashMessage(_('Chyba při přepočtu.'), self::FLASH_ERROR);
            Debugger::log($e);
        }

        $this->redirect('this');
    }

    private function getGradedTasks() {
        $login = $this->getUser()->getIdentity();
        $person = $login->getPerson();
        if (!$person) {
            return array();
        }

        $taskIds = array();
        foreach ($this->seriesTable->getTasks() as $task) {
            $taskIds[] = $task->task_id;
        }
        $gradedTasks = $this->serviceTaskContribution->getTable()
                        ->where(array(
                            'person_id' => $person->person_id,
                            'task_id' => $taskIds,
                            'type' => ModelTaskContribution::TYPE_GRADE
                        ))->fetchPairs('task_id', 'task_id');
        return array_values($gradedTasks);
    }

}
