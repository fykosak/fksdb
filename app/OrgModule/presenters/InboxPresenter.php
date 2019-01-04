<?php

namespace OrgModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\FormControl\OptimisticFormControl;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Controls\ContestantSubmits;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\ORM\ModelContestant;
use FKSDB\ORM\ModelSubmit;
use FKSDB\ORM\ModelTask;
use FKSDB\ORM\ModelTaskContribution;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Security\Permission;
use ServiceContestant;
use ServicePerson;
use ServiceSubmit;
use ServiceTaskContribution;
use Submits\ISubmitStorage;
use Submits\SeriesTable;

class InboxPresenter extends SeriesPresenter {

    const POST_CT_ID = 'ctId';
    const POST_ORDER = 'order';
    const TASK_PREFIX = 'task';

    /**
     * @var ISubmitStorage
     */
    private $submitStorage;

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

    public function injectSubmitStorage(ISubmitStorage $submitStorage) {
        $this->submitStorage = $submitStorage;
    }

    public function injectServiceTaskContribution(ServiceTaskContribution $serviceTaskContribution) {
        $this->serviceTaskContribution = $serviceTaskContribution;
    }

    public function injectServicePerson(ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    public function injectServiceSubmit(ServiceSubmit $serviceSubmit) {
        $this->serviceSubmit = $serviceSubmit;
    }

    public function injectServiceContestant(ServiceContestant $serviceContestant) {
        $this->serviceContestant = $serviceContestant;
    }

    public function injectSeriesTable(SeriesTable $seriesTable) {
        $this->seriesTable = $seriesTable;
    }

    public function injectPersonFactory(PersonFactory $personFactory) {
        $this->personFactory = $personFactory;
    }

    protected function startup() {
        parent::startup();
        $this->seriesTable->setContest($this->getSelectedContest());
        $this->seriesTable->setYear($this->getSelectedYear());
        $this->seriesTable->setSeries($this->getSelectedSeries());
    }

    public function authorizedDefault() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('submit', Permission::ALL, $this->getSelectedContest()));
    }

    public function authorizedHandout() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('task', 'edit', $this->getSelectedContest()));
    }

    public function titleDefault() {
        $this->setTitle(_('Příjem řešení'));
        $this->setIcon('fa fa-envelope-open');
    }

    public function renderDefault() {
        $this['inboxForm']->getForm()->setDefaults();
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
        $this['handoutForm']->getForm()->setDefaults($values);
    }

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
            $control = new ContestantSubmits($tasks, $contestant, $this->serviceSubmit, $this->getSelectedAcademicYear(), $contestant->getPerson()->getFullname());
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

    protected function createComponentHandoutForm() {
        $formControl = new FormControl();
        $form = $formControl->getForm();

        foreach ($this->seriesTable->getTasks() as $row) {
            $task = ModelTask::createFromTableRow($row);
            $control = $this->personFactory->createPersonSelect(false, $task->getFQName(), $this->getOrgProvider());
            $control->setMultiselect(true);
            $form->addComponent($control, self::TASK_PREFIX . $task->task_id);
        }

        $form->addSubmit('save', _('Uložit'));
        $form->onSuccess[] = callback($this, 'handoutFormSuccess');

        return $formControl;
    }

    public function inboxFormSuccess(Form $form) {
        $values = $form->getValues();

        $this->serviceSubmit->getConnection()->beginTransaction();

        foreach ($values[SeriesTable::FORM_CONTESTANT] as $container) {
            $submits = $container[SeriesTable::FORM_SUBMIT];

            foreach ($submits as $row) {
                $submit = ModelSubmit::createFromTableRow($row);
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

    public function handoutFormSuccess(Form $form) {
        $values = $form->getValues();

        $ORMservice = $this->serviceTaskContribution;
        $connection = $ORMservice->getConnection();

        $connection->beginTransaction();

        foreach ($this->seriesTable->getTasks() as $row) {
            $task = ModelTask::createFromTableRow($row);
            $ORMservice->getTable()->where(array(
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
                $contribution = $ORMservice->createNew($data);
                $ORMservice->save($contribution);
            }
        }

        $connection->commit();

        $this->flashMessage(_('Přiřazení opravovatelů uloženo.'), self::FLASH_SUCCESS);
        $this->redirect('this');
    }

    private $orgProvider;

    private function getOrgProvider() {
        if (!$this->orgProvider) {
            $this->orgProvider = new PersonProvider($this->servicePerson);
            $this->orgProvider->filterOrgs($this->getSelectedContest(), $this->yearCalculator);
        }
        return $this->orgProvider;
    }

}
