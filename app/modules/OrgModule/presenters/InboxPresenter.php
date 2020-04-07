<?php

namespace OrgModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\FormControl\OptimisticFormControl;
use FKSDB\Components\Controls\Upload\CheckSubmitsControl;
use FKSDB\Components\Controls\Upload\CorrectedFormControl;
use FKSDB\Components\Controls\Upload\PointsTableControl;
use FKSDB\Components\Controls\Upload\SubmitsTableControl;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Controls\ContestantSubmits;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Models\ModelTask;
use FKSDB\ORM\Models\ModelTaskContribution;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\ORM\Services\ServiceTaskContribution;
use FKSDB\Submits\SeriesTable;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Security\Permission;

/**
 * Class InboxPresenter
 * @package OrgModule
 */
class InboxPresenter extends SeriesPresenter {

    const TASK_PREFIX = 'task';

    /**
     * @var ServiceTaskContribution
     */
    private $serviceTaskContribution;

    /**
     * @var ServicePerson
     */
    private $servicePerson;

    /**
     * @var ServiceSubmit
     */
    private $serviceSubmit;

    /**
     * @var SeriesTable
     */
    private $seriesTable;

    /**
     * @var PersonFactory
     */
    private $personFactory;

    /**
     * @param ServiceTaskContribution $serviceTaskContribution
     */
    public function injectServiceTaskContribution(ServiceTaskContribution $serviceTaskContribution) {
        $this->serviceTaskContribution = $serviceTaskContribution;
    }

    /**
     * @param ServicePerson $servicePerson
     */
    public function injectServicePerson(ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    /**
     * @param ServiceSubmit $serviceSubmit
     */
    public function injectServiceSubmit(ServiceSubmit $serviceSubmit) {
        $this->serviceSubmit = $serviceSubmit;
    }

    /**
     * @param SeriesTable $seriesTable
     */
    public function injectSeriesTable(SeriesTable $seriesTable) {
        $this->seriesTable = $seriesTable;
    }

    /**
     * @param PersonFactory $personFactory
     */
    public function injectPersonFactory(PersonFactory $personFactory) {
        $this->personFactory = $personFactory;
    }
    /* ***************** AUTH ***********************/
    /**
     * @throws BadRequestException
     */
    public function authorizedDefault() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('submit', Permission::ALL, $this->getSelectedContest()));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedList() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('submit', 'list', $this->getSelectedContest()));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedHandout() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('task', 'edit', $this->getSelectedContest()));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedPoints() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('submit', 'points', $this->getSelectedContest()));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedCorrected() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('submit', 'corrected', $this->getSelectedContest()));
    }

    /* ***************** TITLES ***********************/
    public function titleDefault() {
        $this->setTitle(_('Inbox'), 'fa fa-envelope-open');
    }

    public function titleHandout() {
        $this->setTitle(_('Rozdělení úloh opravovatelům'), 'fa fa-inbox');
    }

    public function titleList() {
        $this->setTitle(_('List of solutions'), 'fa fa-cloud-download');
    }

    public function titleCorrected() {
        $this->setTitle(_('Corrected'), 'fa fa-inbox');
    }

    public function titlePoints() {
        $this->setTitle(_('Points'), 'fa fa-inbox');
    }

    /* *********** LIVE CYCLE *************/
    /**
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    protected function startup() {
        parent::startup();
        $this->seriesTable->setContest($this->getSelectedContest());
        $this->seriesTable->setYear($this->getSelectedYear());
        $this->seriesTable->setSeries($this->getSelectedSeries());
    }

    public function actionHandout() {
        // This workaround fixes inproper caching of referenced tables.
        $connection = $this->servicePerson->getConnection();
        $connection->getCache()->clean([Cache::ALL => true]);
        $connection->getDatabaseReflection()->setConnection($connection);
    }

    /* ******************** RENDER ****************/
    /**
     * @throws BadRequestException
     */
    public function renderDefault() {
        /** @var OptimisticFormControl $control */
        $control = $this->getComponent('inboxForm');
        $control->getForm()->setDefaults();
    }

    /**
     * @throws BadRequestException
     */
    public function renderHandout() {
        $taskIds = [];
        foreach ($this->seriesTable->getTasks() as $row) {
            $task = ModelTask::createFromActiveRow($row);
            $taskIds[] = $task->task_id;
        }
        $contributions = $this->serviceTaskContribution->getTable()->where([
            'type' => ModelTaskContribution::TYPE_GRADE,
            'task_id' => $taskIds,
        ]);

        $values = [];
        foreach ($contributions as $row) {
            $contribution = ModelTaskContribution::createFromActiveRow($row);
            $taskId = $contribution->task_id;
            $personId = $contribution->person_id;
            $key = self::TASK_PREFIX . $taskId;
            if (!isset($values[$key])) {
                $values[$key] = [];
            }
            $values[$key][] = $personId;
        }
        /** @var FormControl $control */
        $control = $this->getComponent('handoutForm');
        $control->getForm()->setDefaults($values);

    }
    /* ******************* COMPONENTS ******************/
    /**
     * @return OptimisticFormControl
     * @throws BadRequestException
     */
    protected function createComponentInboxForm() {
        $controlForm = new OptimisticFormControl(
            function () {
                return $this->seriesTable->getFingerprint();
            },
            function () {
                return $this->seriesTable->formatAsFormValues();
            }
        );
        $form = $controlForm->getForm();

        $contestants = $this->seriesTable->getContestants();
        $tasks = $this->seriesTable->getTasks();
        $container = new ModelContainer();
        $form->addComponent($container, SeriesTable::FORM_CONTESTANT);

        foreach ($contestants as $row) {
            $contestant = ModelContestant::createFromActiveRow($row);
            $control = new ContestantSubmits($tasks, $contestant, $this->serviceSubmit, $this->getSelectedAcademicYear(), $contestant->getPerson()->getFullName());
            $control->setClassName('inbox');
            $namingContainer = new ModelContainer();
            $container->addComponent($namingContainer, $contestant->ct_id);
            $namingContainer->addComponent($control, SeriesTable::FORM_SUBMIT);
        }

        $form->addSubmit('save', _('Save'));
        $form->onSuccess[] = function (Form $form) {
            $this->inboxFormSuccess($form);
        };

        // JS dependencies
        $this->registerJSFile('js/datePicker.js');
        $this->registerJSFile('js/jquery.ui.swappable.js');
        $this->registerJSFile('js/inbox.js');

        return $controlForm;
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     */
    protected function createComponentHandoutForm() {
        $formControl = new FormControl();
        $form = $formControl->getForm();
        $orgProvider = new PersonProvider($this->servicePerson);
        $orgProvider->filterOrgs($this->getSelectedContest(), $this->yearCalculator);

        foreach ($this->seriesTable->getTasks() as $row) {
            $task = ModelTask::createFromActiveRow($row);
            $control = $this->personFactory->createPersonSelect(false, $task->getFQName(), $orgProvider);
            $control->setMultiSelect(true);
            $form->addComponent($control, self::TASK_PREFIX . $task->task_id);
        }

        $form->addSubmit('save', _('Save'));
        $form->onSuccess[] = function (Form $form) {
            $this->handoutFormSuccess($form);
        };

        return $formControl;
    }

    /**
     * @return CorrectedFormControl
     */
    public function createComponentCorrectedFormControl(): CorrectedFormControl {
        return new CorrectedFormControl($this->getContext(), $this->seriesTable);
    }

    /**
     * @return CheckSubmitsControl
     */
    protected function createComponentCheckControl(): CheckSubmitsControl {
        return new CheckSubmitsControl($this->getContext(), $this->seriesTable);
    }

    /**
     * @return SubmitsTableControl
     */
    protected function createComponentSubmitsTableControl(): SubmitsTableControl {
        return new SubmitsTableControl($this->getContext(), $this->seriesTable);
    }

    /**
     * @return PointsTableControl
     */
    protected function createComponentPointsTableControl(): PointsTableControl {
        return new PointsTableControl($this->getContext(), $this->seriesTable);
    }

    /**
     * @param Form $form
     * @throws AbortException
     */
    public function inboxFormSuccess(Form $form) {
        $values = $form->getValues();

        $this->serviceSubmit->getConnection()->beginTransaction();

        foreach ($values[SeriesTable::FORM_CONTESTANT] as $container) {
            $submits = $container[SeriesTable::FORM_SUBMIT];

            foreach ($submits as $submit) {
                /** @var ModelSubmit $submit */
                // ACL granularity is very rough, we just check it in action* method
                if ($submit->isEmpty()) {
                    $this->serviceSubmit->dispose($submit);
                } else {
                    $this->serviceSubmit->save($submit);
                }
            }
        }
        $this->serviceSubmit->getConnection()->commit();
        $this->flashMessage(_('Informace o řešeních uložena.'), self::FLASH_SUCCESS);
        $this->redirect('this');
    }

    /**
     * @param Form $form
     * @throws AbortException
     */
    public function handoutFormSuccess(Form $form) {
        $values = $form->getValues();

        $service = $this->serviceTaskContribution;
        $connection = $service->getConnection();

        $connection->beginTransaction();

        foreach ($this->seriesTable->getTasks() as $row) {
            $task = ModelTask::createFromActiveRow($row);
            $service->getTable()->where([
                'task_id' => $task->task_id,
                'type' => ModelTaskContribution::TYPE_GRADE
            ])->delete();
            $key = self::TASK_PREFIX . $task->task_id;
            foreach ($values[$key] as $personId) {
                $data = [
                    'task_id' => $task->task_id,
                    'person_id' => $personId,
                    'type' => ModelTaskContribution::TYPE_GRADE,
                ];
                $this->serviceTaskContribution->createNewModel($data);
            }
        }

        $connection->commit();

        $this->flashMessage(_('Přiřazení opravovatelů uloženo.'), self::FLASH_SUCCESS);
        $this->redirect('this');
    }
}
