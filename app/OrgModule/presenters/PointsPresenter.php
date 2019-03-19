<?php

namespace OrgModule;

use Exception;
use FKSDB\Components\Forms\Controls\ContestantSubmits;
use FKSDB\Components\Forms\OptimisticForm;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelLogin;
use FKSDB\ORM\Models\ModelTaskContribution;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\ORM\Services\ServiceTask;
use FKSDB\ORM\Services\ServiceTaskContribution;
use FKSDB\ORM\Services\ServiceTaskStudyYear;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use Nette\InvalidArgumentException;
use Nette\Utils\Html;
use SQLResultsCache;
use Submits\SeriesTable;

/**
 * Class PointsPresenter
 * @package OrgModule
 */
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
     * @var \FKSDB\ORM\Services\ServiceSubmit
     */
    private $serviceSubmit;

    /**
     * @var \FKSDB\ORM\Services\ServiceTask
     */
    private $serviceTask;

    /**
     * @var \FKSDB\ORM\Services\ServiceTaskContribution
     */
    private $serviceTaskContribution;

    /**
     * @var \FKSDB\ORM\Services\ServiceTaskStudyYear
     */
    private $serviceTaskStudyYear;

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
     * @param \FKSDB\ORM\Services\ServiceSubmit $serviceSubmit
     */
    public function injectServiceSubmit(ServiceSubmit $serviceSubmit) {
        $this->serviceSubmit = $serviceSubmit;
    }

    /**
     * @param \FKSDB\ORM\Services\ServiceTask $serviceTask
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

    /**
     * @param ServiceTaskStudyYear $serviceTaskStudyYear
     */
    public function injectServiceTaskStudyYear(ServiceTaskStudyYear $serviceTaskStudyYear) {
        $this->serviceTaskStudyYear = $serviceTaskStudyYear;
    }

    protected function startup() {
        parent::startup();
        $this->seriesTable->setContest($this->getSelectedContest());
        $this->seriesTable->setYear($this->getSelectedYear());
        $this->seriesTable->setSeries($this->getSelectedSeries());
    }

    /**
     * @throws \Nette\Application\BadRequestException
     */
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
        $this->setIcon('fa fa-trophy');
        $this->setTitle(sprintf(_('Zadávání bodů %d. série'), $this->getSelectedSeries()));
    }

    public function renderDefault() {
        $this->getComponent('pointsForm')->getForm()->setDefaults();
        $this->template->showAll = (bool)$this->all;
    }

    /**
     * @param $name
     * @return OptimisticForm
     * @throws \Nette\Application\BadRequestException
     */
    protected function createComponentPointsForm($name) {
        //   $controlContainer = new FormControl();
        //   $formToRemove = $controlContainer->getForm();
        //  $controlContainer->removeComponent($formToRemove);

        $form = new OptimisticForm(
            [$this->seriesTable, 'getFingerprint'], [$this->seriesTable, 'formatAsFormValues']
        );
        // $controlContainer->addComponent($form, 'form');


        $contestants = $this->seriesTable->getContestants();
        $tasks = $this->seriesTable->getTasks();
        $gradedTasks = $this->getGradedTasks();

        $container = $form->addContainer(SeriesTable::FORM_CONTESTANT);

        foreach ($contestants as $row) {
            $contestant = ModelContestant::createFromTableRow($row);
            $fullname = $contestant->getPerson()->getFullName();
            $schoolAbbrev = $contestant->getPerson()->getHistory($this->getSelectedAcademicYear())->getSchool()->name_abbrev;
            $schoolLabel = Html::el('small');
            $schoolLabel->setText('(' . $schoolAbbrev . ')');
            $schoolLabel->class = 'text-muted';
            $label = Html::el()
                ->setText($fullname)
                ->add(Html::el('br'))
                ->add($schoolLabel);
            $control = new ContestantSubmits($tasks, $contestant, $this->serviceSubmit, $this->getSelectedAcademicYear(), $label);
            $control->setClassName('points');
            // $namingContainer = new ContainerWithOptions();
            // $container->addComponent($namingContainer,$contestant->ct_id);
            $namingContainer = $container->addContainer($contestant->ct_id);
            $namingContainer->addComponent($control, SeriesTable::FORM_SUBMIT);
        }

        $form->addSubmit('save', _('Uložit'));
        $form->onSuccess[] = array($this, 'pointsFormSuccess');

        // JS dependencies
        $this->registerJSFile('js/points.js');

        return $form;
    }

    /**
     * @param Form $form
     * @throws \Nette\Application\AbortException
     */
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
        } catch (Exception $exception) {
            $this->flashMessage(_('Chyba při ukládání bodů.'), self::FLASH_ERROR);
            Debugger::log($exception);
        }
        $this->redirect('this');
    }

    /**
     * @throws \Nette\Application\AbortException
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
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
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
        } catch (InvalidArgumentException $exception) {
            $this->flashMessage(_('Chyba při přepočtu.'), self::FLASH_ERROR);
            Debugger::log($exception);
        }

        $this->redirect('this');
    }

    /**
     * @return array
     */
    private function getGradedTasks() {
        /**
         * @var ModelLogin $login
         */
        $login = $this->getUser()->getIdentity();
        $person = $login->getPerson();
        if (!$person) {
            return [];
        }

        $taskIds = [];
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
