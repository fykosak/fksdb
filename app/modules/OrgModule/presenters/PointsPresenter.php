<?php

namespace OrgModule;

use Exception;
use FKSDB\Components\Forms\Controls\ContestantSubmits;
use FKSDB\Components\Forms\OptimisticForm;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelLogin;
use FKSDB\ORM\Models\ModelTask;
use FKSDB\ORM\Models\ModelTaskContribution;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\ORM\Services\ServiceTask;
use FKSDB\ORM\Services\ServiceTaskContribution;
use FKSDB\ORM\Services\ServiceTaskStudyYear;
use FKSDB\Results\SQLResultsCache;
use FKSDB\Submits\SeriesTable;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Tracy\Debugger;
use Nette\InvalidArgumentException;
use Nette\Utils\Html;

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
     * @param ServiceSubmit $serviceSubmit
     */
    public function injectServiceSubmit(ServiceSubmit $serviceSubmit) {
        $this->serviceSubmit = $serviceSubmit;
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
     * @throws BadRequestException
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
        $this->setTitle(sprintf(_('Zadávání bodů %d. série'), $this->getSelectedSeries()), 'fa fa-trophy');
    }

    public function renderDefault() {
        $this->getComponent('pointsForm')->getForm()->setDefaults();
        $this->template->showAll = (bool)$this->all;
    }

    /**
     * @return OptimisticForm
     * @throws BadRequestException
     */
    protected function createComponentPointsForm(): OptimisticForm {

        $form = new OptimisticForm(
            function () {
                return $this->seriesTable->getFingerprint();
            },
            function () {
                return $this->seriesTable->formatAsFormValues();
            }
        );
        $contestants = $this->seriesTable->getContestants();
        $tasks = $this->seriesTable->getTasks();
        // $gradedTasks = $this->getGradedTasks();

        $container = $form->addContainer(SeriesTable::FORM_CONTESTANT);

        foreach ($contestants as $row) {
            $contestant = ModelContestant::createFromActiveRow($row);
            $fullName = $contestant->getPerson()->getFullName();
            $schoolAbbrev = $contestant->getPerson()->getHistory($this->getSelectedAcademicYear())->getSchool()->name_abbrev;
            $schoolLabel = Html::el('small');
            $schoolLabel->setText('(' . $schoolAbbrev . ')');
            $schoolLabel->class = 'text-muted';
            $label = Html::el('span')
                ->setText($fullName)
                ->addHtml(Html::el('br'))
                ->addText($schoolLabel);
            $control = new ContestantSubmits($tasks, $contestant, $this->serviceSubmit, $this->getSelectedAcademicYear(), $label);
            $control->setClassName('points');
            $namingContainer = $container->addContainer($contestant->ct_id);
            $namingContainer->addComponent($control, SeriesTable::FORM_SUBMIT);
        }

        $form->addSubmit('save', _('Save'));
        $form->onSuccess[] = function (Form $form) {
            return $this->pointsFormSuccess($form);
        };

        // JS dependencies
        $this->registerJSFile('js/points.js');

        return $form;
    }

    /**
     * @param Form $form
     * @throws AbortException
     */
    private function pointsFormSuccess(Form $form) {
        $values = $form->getValues();

        try {
            $this->serviceSubmit->getConnection()->beginTransaction();

            foreach ($values[SeriesTable::FORM_CONTESTANT] as $container) {
                $submits = $container[SeriesTable::FORM_SUBMIT];
                foreach ($submits as $submit) {
                    if (!$submit->isEmpty()) {
                        // TODO
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
    private function getGradedTasks() {
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

}
