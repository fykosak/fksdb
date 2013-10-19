<?php

namespace OrgModule;

use DbNames;
use FKSDB\Components\Forms\Controls\ContestantSubmits;
use FKSDB\Components\Forms\OptimisticForm;
use ModelSubmit;
use ModelTaskContribution;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Security\Permission;
use OOB\MultipleTextSelect;
use Persons\OrgsCompletionModel;
use ServiceContestant;
use ServiceOrg;
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
     * @var ServiceOrg
     */
    private $serviceOrg;

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

    public function injectSubmitStorage(ISubmitStorage $submitStorage) {
        $this->submitStorage = $submitStorage;
    }

    public function injectServiceTaskContribution(ServiceTaskContribution $serviceTaskContribution) {
        $this->serviceTaskContribution = $serviceTaskContribution;
    }

    public function injectServiceOrg(ServiceOrg $serviceOrg) {
        $this->serviceOrg = $serviceOrg;
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

    protected function startup() {
        parent::startup();
        $this->seriesTable->setContest($this->getSelectedContest());
        $this->seriesTable->setYear($this->getSelectedYear());
        $this->seriesTable->setSeries($this->getSelectedSeries());
    }

    public function actionDefault() {
        if (!$this->getContestAuthorizator()->isAllowed('submit', Permission::ALL, $this->getSelectedContest())) {
            throw new BadRequestException('Nedostatečné oprávnění.', 403);
        }
    }

    public function actionHandout() {
        if (!$this->getContestAuthorizator()->isAllowed('task', 'edit', $this->getSelectedContest())) {
            throw new BadRequestException('Nedostatečné oprávnění.', 403);
        }
    }

    public function renderDefault() {
        $this['inboxForm']->setDefaults();
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
            $orgId = $contribution->org_id;
            $key = self::TASK_PREFIX . $taskId;
            if (!isset($values[$key])) {
                $values[$key] = array();
            }
            $values[$key][] = $orgId;
        }
        $this['handoutForm']->setDefaults($values);
    }

    protected function createComponentInboxForm($name) {
        $form = new OptimisticForm(
                array($this->seriesTable, 'getFingerprint'), array($this->seriesTable, 'formatAsFormValues')
        );

        $contestants = $this->seriesTable->getContestants();
        $tasks = $this->seriesTable->getTasks();


        $container = $form->addContainer(SeriesTable::FORM_CONTESTANT);

        foreach ($contestants as $contestant) {
            $control = new ContestantSubmits($tasks, $contestant, $this->serviceSubmit, $contestant->getPerson()->getFullname());
            $control->setClassName('inbox');

            $namingContainer = $container->addContainer($contestant->ct_id);
            $namingContainer->addComponent($control, SeriesTable::FORM_SUBMIT);
        }

        $form->addSubmit('save', 'Uložit');
        $form->onSuccess[] = array($this, 'inboxFormSuccess');

        // JS dependencies        
        $this->registerJSFile('js/datePicker.js');
        $this->registerJSFile('js/jquery.ui.swappable.js');
        $this->registerJSFile('js/inbox.js');

        return $form;
    }

    protected function createComponentHandoutForm() {
        $form = new Form();

        $model = $this->getOrgsModel();
        foreach ($this->seriesTable->getTasks() as $task) {
            $control = new MultipleTextSelect($model, $task->getFQName());
            $control->setUnknownMode(MultipleTextSelect::N_INVALID);
            $control->addRule(Form::VALID, 'Neznámý organizátor u úlohy %label.');
            $form->addComponent($control, self::TASK_PREFIX . $task->task_id);
        }

        $form->addSubmit('save', 'Uložit');
        $form->onSuccess[] = callback($this, 'handoutFormSuccess');

        return $form;
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
        $this->flashMessage('Informace o řešeních uložena.');
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
            foreach ($values[$key] as $orgId) {
                $data = array(
                    'task_id' => $task->task_id,
                    'org_id' => $orgId,
                    'type' => ModelTaskContribution::TYPE_GRADE,
                );
                $contribution = $ORMservice->createNew($data);
                $ORMservice->save($contribution);
            }
        }

        $connection->commit();

        $this->flashMessage('Přiřazení opravovatelů uloženo.');
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
        $tempDir = $this->context->parameters['tempDir']; // TODO is this right way (TM)? how else it could be done?
        $backup = tempnam($tempDir, 'restamp');
        copy($filename, $backup);

        $this->submitStorage->deleteFile($oldSubmit); //TODO include in the transaction?

        $this->submitStorage->storeFile($backup, $newSubmit);
        // backup file is renamed in file storage
    }

    private $orgsModel;

    private function getOrgsModel() {
        if (!$this->orgsModel) {
            $this->orgsModel = new OrgsCompletionModel($this->getSelectedContest(), $this->serviceOrg, $this->yearCalculator);
        }
        return $this->orgsModel;
    }

}
