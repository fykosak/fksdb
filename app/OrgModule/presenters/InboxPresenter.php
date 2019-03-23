<?php

namespace OrgModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\FormControl\OptimisticFormControl;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Controls\ContestantSubmits;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Models\ModelTask;
use FKSDB\ORM\Models\ModelTaskContribution;
use FKSDB\ORM\Services\ServiceContestant;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\ORM\Services\ServiceTaskContribution;
use FKSDB\Submits\ISubmitStorage;
use FKSDB\Submits\SeriesTable;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Security\Permission;

/**
 * Class InboxPresenter
 * @package OrgModule
 */
class InboxPresenter extends SeriesPresenter {

    const POST_CT_ID = 'ctId';
    const POST_ORDER = 'order';
    const TASK_PREFIX = 'task';

    /**
     * @var ISubmitStorage
     */
    private $submitStorage;

    /**
     * @var \FKSDB\ORM\Services\ServiceTaskContribution
     */
    private $serviceTaskContribution;

    /**
     * @var \FKSDB\ORM\Services\ServicePerson
     */
    private $servicePerson;

    /**
     * @var \FKSDB\ORM\Services\ServiceSubmit
     */
    private $serviceSubmit;

    /**
     * @var ServiceContestant
     */
    private $serviceContestant;

    /**
     * @var SeriesTable
     */
    private $seriesTable;

    /**
     * @var PersonFactory
     */
    private $personFactory;

    /**
     * @param ISubmitStorage $submitStorage
     */
    public function injectSubmitStorage(ISubmitStorage $submitStorage) {
        $this->submitStorage = $submitStorage;
    }

    /**
     * @param ServiceTaskContribution $serviceTaskContribution
     */
    public function injectServiceTaskContribution(ServiceTaskContribution $serviceTaskContribution) {
        $this->serviceTaskContribution = $serviceTaskContribution;
    }

    /**
     * @param \FKSDB\ORM\Services\ServicePerson $servicePerson
     */
    public function injectServicePerson(ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    /**
     * @param \FKSDB\ORM\Services\ServiceSubmit $serviceSubmit
     */
    public function injectServiceSubmit(ServiceSubmit $serviceSubmit) {
        $this->serviceSubmit = $serviceSubmit;
    }

    /**
     * @param \FKSDB\ORM\Services\ServiceContestant $serviceContestant
     */
    public function injectServiceContestant(ServiceContestant $serviceContestant) {
        $this->serviceContestant = $serviceContestant;
    }

    /**
     * @param \FKSDB\Submits\SeriesTable $seriesTable
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
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('submit', Permission::ALL, $this->getSelectedContest()));
    }

    /**
     * @throws \Nette\Application\BadRequestException
     */
    public function authorizedHandout() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('task', 'edit', $this->getSelectedContest()));
    }

    public function titleDefault() {
        $this->setTitle(_('Příjem řešení'));
        $this->setIcon('fa fa-envelope-open');
    }

    /**
     * @throws \Nette\Application\BadRequestException
     */
    public function renderDefault() {
        /**
         * @var OptimisticFormControl $control
         */
        $control = $this->getComponent('inboxForm');
        $control->getForm()->setDefaults();
    }

    public function titleHandout() {
        $this->setTitle(_('Rozdělení úloh opravovatelům'));
        $this->setIcon('fa fa-inbox');
    }

    public function actionHandout() {
        // This workaround fixes inproper caching of referenced tables.
        $connection = $this->servicePerson->getConnection();
        $connection->getCache()->clean(array(Cache::ALL => true));
        $connection->getDatabaseReflection()->setConnection($connection);
    }

    /**
     * @throws \Nette\Application\BadRequestException
     */
    public function renderHandout() {
        $taskIds = [];
        foreach ($this->seriesTable->getTasks() as $row) {
            $task = ModelTask::createFromTableRow($row);
            $taskIds[] = $task->task_id;
        }
        $contributions = $this->serviceTaskContribution->getTable()->where(array(
            'type' => ModelTaskContribution::TYPE_GRADE,
            'task_id' => $taskIds,
        ));

        $values = [];
        foreach ($contributions as $row) {
            $contribution = ModelTaskContribution::createFromTableRow($row);
            $taskId = $contribution->task_id;
            $personId = $contribution->person_id;
            $key = self::TASK_PREFIX . $taskId;
            if (!isset($values[$key])) {
                $values[$key] = [];
            }
            $values[$key][] = $personId;
        }
        /**
         * @var FormControl $control
         */
        $control = $this->getComponent('handoutForm');
        $control->getForm()->setDefaults($values);

    }

    /**
     * @return OptimisticFormControl
     * @throws \Nette\Application\BadRequestException
     */
    protected function createComponentInboxForm() {
        $controlForm = new OptimisticFormControl([$this->seriesTable, 'getFingerprint'], [$this->seriesTable, 'formatAsFormValues']);
        /*$form = new OptimisticForm(
            array($this->seriesTable, 'getFingerprint'), array($this->seriesTable, 'formatAsFormValues')
        );*/
        $form = $controlForm->getForm();
        /*  $renderer = new BootstrapRenderer();
          $renderer->setColLeft(2);
          $renderer->setColRight(10);
          $form->setRenderer($renderer);*/

        $contestants = $this->seriesTable->getContestants();
        $tasks = $this->seriesTable->getTasks();
        $container = new ModelContainer();
        $form->addComponent($container, SeriesTable::FORM_CONTESTANT);
        // $container = $form->addContainer(SeriesTable::FORM_CONTESTANT);

        foreach ($contestants as $row) {
            $contestant = ModelContestant::createFromTableRow($row);
            $control = new ContestantSubmits($tasks, $contestant, $this->serviceSubmit, $this->getSelectedAcademicYear(), $contestant->getPerson()->getFullName());
            $control->setClassName('inbox');
            $namingContainer = new ModelContainer();
            $container->addComponent($namingContainer, $contestant->ct_id);
            // $namingContainer = $container->addContainer($contestant->ct_id);
            $namingContainer->addComponent($control, SeriesTable::FORM_SUBMIT);
        }

        $form->addSubmit('save', _('Uložit'));
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
     * @throws \Nette\Application\BadRequestException
     */
    protected function createComponentHandoutForm() {
        $formControl = new FormControl();
        $form = $formControl->getForm();

        foreach ($this->seriesTable->getTasks() as $row) {
            $task = ModelTask::createFromTableRow($row);
            $control = $this->personFactory->createPersonSelect(false, $task->getFQName(), $this->getOrgProvider());
            $control->setMultiSelect(true);
            $form->addComponent($control, self::TASK_PREFIX . $task->task_id);
        }

        $form->addSubmit('save', _('Uložit'));
        $form->onSuccess[] = callback($this, 'handoutFormSuccess');

        return $formControl;
    }

    /**
     * @param Form $form
     * @throws \Nette\Application\AbortException
     */
    public function inboxFormSuccess(Form $form) {
        $values = $form->getValues();

        $this->serviceSubmit->getConnection()->beginTransaction();

        foreach ($values[SeriesTable::FORM_CONTESTANT] as $container) {
            $submits = $container[SeriesTable::FORM_SUBMIT];

            foreach ($submits as $row) {
                /**
                 * @var ModelSubmit $submit
                 */
                $submit = $row;
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
     * @throws \Nette\Application\AbortException
     */
    public function handoutFormSuccess(Form $form) {
        $values = $form->getValues();

        $service = $this->serviceTaskContribution;
        $connection = $service->getConnection();

        $connection->beginTransaction();

        foreach ($this->seriesTable->getTasks() as $row) {
            $task = ModelTask::createFromTableRow($row);
            $service->getTable()->where(array(
                'task_id' => $task->task_id,
                'type' => ModelTaskContribution::TYPE_GRADE
            ))->delete();
            $key = self::TASK_PREFIX . $task->task_id;
            foreach ($values[$key] as $personId) {
                $data = array(
                    'task_id' => $task->task_id,
                    'person_id' => $personId,
                    'type' => ModelTaskContribution::TYPE_GRADE,
                );
                $contribution = $service->createNew($data);
                $service->save($contribution);
            }
        }

        $connection->commit();

        $this->flashMessage(_('Přiřazení opravovatelů uloženo.'), self::FLASH_SUCCESS);
        $this->redirect('this');
    }

    private $orgProvider;

    /**
     * @return PersonProvider
     * @throws \Nette\Application\BadRequestException
     */
    private function getOrgProvider() {
        if (!$this->orgProvider) {
            $this->orgProvider = new PersonProvider($this->servicePerson);
            $this->orgProvider->filterOrgs($this->getSelectedContest(), $this->yearCalculator);
        }
        return $this->orgProvider;
    }

}
