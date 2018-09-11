<?php

namespace OrgModule;

use DbNames;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Controls\ContestantSubmits;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Forms\OptimisticForm;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use ModelSubmit;
use ModelTaskContribution;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Security\Permission;
use ServiceContestant;
use ServicePerson;
use ServiceSubmit;
use ServiceTaskContribution;
use ServiceTaskStudyYear;
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
        $this['inboxForm']->setDefaults();
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
        $taskIds = array();
        foreach ($this->seriesTable->getTasks() as $task) {
            $taskIds[] = $task->task_id;
        }
        $contributions = $this->serviceTaskContribution->getTable()->where(array(
            'type' => ModelTaskContribution::TYPE_GRADE,
            'task_id' => $taskIds,
        ));

        $values = array();
        foreach ($contributions as $contribution) {
            $taskId = $contribution->task_id;
            $personId = $contribution->person_id;
            $key = self::TASK_PREFIX . $taskId;
            if (!isset($values[$key])) {
                $values[$key] = array();
            }
            $values[$key][] = $personId;
        }
        $this['handoutForm']->getForm()->setDefaults($values);
    }

    protected function createComponentInboxForm($name) {
        $form = new OptimisticForm(
            array($this->seriesTable, 'getFingerprint'), array($this->seriesTable, 'formatAsFormValues')
        );
        $renderer = new BootstrapRenderer();
        $renderer->setColLeft(2);
        $renderer->setColRight(10);
        $form->setRenderer($renderer);

        $contestants = $this->seriesTable->getContestants();
        $tasks = $this->seriesTable->getTasks();

        $container = $form->addContainer(SeriesTable::FORM_CONTESTANT);

        foreach ($contestants as $contestant) {
            $control = new ContestantSubmits($tasks, $contestant, $this->serviceSubmit, $this->getSelectedAcademicYear(), $contestant->getPerson()->getFullname());
            $control->setClassName('inbox');

            $namingContainer = $container->addContainer($contestant->ct_id);
            $namingContainer->addComponent($control, SeriesTable::FORM_SUBMIT);
        }

        $form->addSubmit('save', _('Uložit'));
        $form->onSuccess[] = array($this, 'inboxFormSuccess');

        // JS dependencies
        $this->registerJSFile('js/datePicker.js');
        $this->registerJSFile('js/jquery.ui.swappable.js');
        $this->registerJSFile('js/inbox.js');

        return $form;
    }

    protected function createComponentHandoutForm() {
        $formControl = new FormControl();
        $form = $formControl->getForm();
        $form->setRenderer(new BootstrapRenderer());

        foreach ($this->seriesTable->getTasks() as $task) {
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

            foreach ($submits as $submit) {
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

        foreach ($this->seriesTable->getTasks() as $task) {
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

    public function handleSwapSubmits() {
        if (!$this->isAjax()) {
            throw new BadRequestException('AJAX only.', 405);
        }

        $post = $this->getHttpRequest()->getPost();

        $ctId = $post[self::POST_CT_ID];
        $order = $post[self::POST_ORDER];
        $series = $this->getSelectedSeries();

        $tasks = array();
        foreach ($this->seriesTable->getTasks() as $task) {
            $task->task_id; // stupid touch
            $tasks[$task->tasknr] = $task;
        }

        $uploadSubmits = array();
        $submits = $this->serviceSubmit->getSubmits()->where(array(
            DbNames::TAB_SUBMIT . '.ct_id' => $ctId,
            DbNames::TAB_TASK . '.series' => $series
        ))->order(DbNames::TAB_TASK . '.tasknr');
        foreach ($submits as $row) {
            if ($row->source == ModelSubmit::SOURCE_POST) {
                unset($tasks[$row->tasknr]);
            } else {
                $uploadSubmits[$row->submit_id] = $this->serviceSubmit->createNew($row->toArray());
                $uploadSubmits[$row->submit_id]->setNew(false);
            }
        }
        $nTasks = array(); // reindexed tasks
        foreach ($tasks as $task) {
            $nTasks[] = $task;
        }


        /*
         * Prepare new tasks for properly ordered submit.
         */
        $orderedSubmits = array();
        $orderedTasks = array();

        $nr = -1;
        foreach ($order as $submitData) {
            ++$nr;
            list($text, $submitId) = explode('-', $submitData);
            if ($submitId == 'null') {
                continue;
            }
            $orderedSubmits[] = $uploadSubmits[$submitId];
            $orderedTasks[] = $nTasks[$nr]->task_id;
        }

        /*
         * Create ORM copies of submits and delete old, then save the new ones
         * (two-pass because of unique constraint).
         */
        $connection = $this->serviceSubmit->getConnection();
        $connection->beginTransaction();

        $newSubmits = array();
        foreach (array_combine($orderedTasks, $orderedSubmits) as $taskId => $submit) {
            if ($taskId == $submit->task_id) {
                $newSubmits[] = $submit;
            } else {
                $data = $submit->toArray();
                unset($data['submit_id']);
                $newSubmit = $this->serviceSubmit->createNew($data);
                $newSubmit->task_id = $taskId;

                $submit->getTask(); // stupid touch
                $this->serviceSubmit->dispose($submit);

                $newSubmits[] = $newSubmit;
            }
        }

        for ($i = 0; $i < count($newSubmits); ++$i) {
            $this->serviceSubmit->save($newSubmits[$i]);
        }

        /*
         * Store files with the new submits.
         */
        $this->submitStorage->beginTransaction();

        foreach (array_keys($orderedSubmits) as $i) {
            $this->restampSubmit($orderedSubmits[$i], $newSubmits[$i]);
        }

        $this->submitStorage->commit();
        $connection->commit();

        /**
         * Prepare AJAX response
         */
        $contestant = $this->serviceContestant->findByPrimary($ctId);
        $submits = $this->seriesTable->getSubmitsTable($ctId);
        $dummyElement = new ContestantSubmits($this->seriesTable->getTasks(), $contestant, $this->serviceSubmit);
        $dummyElement->setValue($submits);

        $this->payload->data = json_decode($dummyElement->getRawValue()); // sorry, back and forth
        $this->payload->fingerprint = $this->seriesTable->getFingerprint();
        $this->sendPayload();
    }

    /**
     *
     * @param ModelSubmit $oldSubmit
     * @param ModelSubmit $newSubmit
     * @return void
     */
    private function restampSubmit(ModelSubmit $oldSubmit, ModelSubmit $newSubmit) {
        if ($oldSubmit->submit_id == $newSubmit->submit_id) {
            return;
        }

        $filename = $this->submitStorage->retrieveFile($oldSubmit, ISubmitStorage::TYPE_ORIGINAL);
        $tempDir = $this->globalParameters['tempDir'];
        $backup = tempnam($tempDir, 'restamp');
        copy($filename, $backup);

        $this->submitStorage->deleteFile($oldSubmit); //TODO include in the transaction?

        $this->submitStorage->storeFile($backup, $newSubmit);
        // backup file is renamed in file storage
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
