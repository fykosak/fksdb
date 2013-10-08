<?php

namespace OrgModule;

use DbNames;
use FKSDB\Components\Forms\Controls\ContestantSubmits;
use FKSDB\Components\Forms\OptimisticForm;
use ModelSubmit;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Security\Permission;
use ServiceSubmit;
use ServiceTask;
use Submits\ISubmitStorage;

class InboxPresenter extends TaskTimesContestantPresenter {

    const POST_CT_ID = 'ctId';
    const POST_ORDER = 'order';

    /**
     * @var ServiceSubmit
     */
    private $serviceSubmit;

    /**
     *
     * @var ServiceTask
     */
    private $serviceTask;

    /**
     * @var ISubmitStorage
     */
    private $submitStorage;

    public function injectSubmitService(ServiceSubmit $submitService) {
        $this->serviceSubmit = $submitService;
    }

    public function injectServiceTask(ServiceTask $serviceTask) {
        $this->serviceTask = $serviceTask;
    }

    public function injectSubmitStorage(ISubmitStorage $submitStorage) {
        $this->submitStorage = $submitStorage;
    }

    public function actionDefault() {
        if (!$this->getContestAuthorizator()->isAllowed('submit', Permission::ALL, $this->getSelectedContest())) {
            throw new BadRequestException('Nedostatečné oprávnění.', 403);
        }
    }

    public function renderDefault() {
        $this->template->contestants = $this->getContestants();
        $this->template->tasks = $this->getTasks()->fetchPairs('task_id');

        $this['inboxForm']->setDefaults();
    }

    protected function createComponentInboxForm($name) {
        $form = new OptimisticForm(
                array($this, 'inboxFormDataFingerprint'), array($this, 'inboxFormDefaultValues')
        );

        $contestants = $this->getContestants();
        $tasks = $this->getTasks();


        $container = $form->addContainer('contestants');

        foreach ($contestants as $contestant) {
            $control = new ContestantSubmits($tasks, $contestant, $this->serviceSubmit, $contestant->getPerson()->getFullname());
            $control->setClassName('inbox');

            $namingContainer = $container->addContainer($contestant->ct_id);
            $namingContainer->addComponent($control, 'submit');
        }

        $form->addSubmit('save', 'Uložit');
        $form->onSuccess[] = array($this, 'inboxFormSuccess');

        return $form;
    }

    public function inboxFormSuccess(Form $form) {
        $values = $form->getValues();

        $this->serviceSubmit->getConnection()->beginTransaction();

        foreach ($values['contestants'] as $container) {
            $submits = $container['submit'];
            //dump($submits);
            foreach ($submits as $submit) {
                // ACL granularity is very rough, we just check it in action* method
                if ($submit->isEmpty()) {
                    $this->serviceSubmit->dispose($submit);
                } else {
                    if ($submit->submit_id && $submit->getOriginalTaskId() != $submit->task_id) {
                        $this->flashMessage($submit->submit_id . ": changed task from {$submit->getOriginalTaskId()} to {$submit->task_id}.");
                    } else { // to prevent doing it uncontrollable
                        $this->serviceSubmit->save($submit);
                    }
                }
            }
        }
        $this->serviceSubmit->getConnection()->commit();
        $this->flashMessage('Informace o řešeních uložena.');
        $this->redirect('this');
    }

    /**
     * @internal
     * @return array
     */
    public function inboxFormDefaultValues() {
        $submitsTable = $this->getSubmitsTable();
        $contestants = $this->getContestants();
        $result = array();
        foreach ($contestants as $contestant) {
            $ctId = $contestant->ct_id;
            if (isset($submitsTable[$ctId])) {
                $result[$ctId] = array('submit' => $submitsTable[$ctId]);
            } else {
                $result[$ctId] = array('submit' => null);
            }
        }
        return array(
            'contestants' => $result
        );
    }

    /**
     * @internal
     * @return array
     */
    public function inboxFormDataFingerprint() {
        $fingerprint = '';
        foreach ($this->getSubmitsTable() as $submits) {
            foreach ($submits as $submit) {
                $fingerprint .= $submit->getFingerprint();
            }
        }
        return md5($fingerprint);
    }

    public function handleSwapSubmits() {
        if (!$this->isAjax()) {
            throw new BadRequestException('AJAX only.', 405);
        }

        $post = $this->getHttpRequest()->getPost();

        $ctId = $post[self::POST_CT_ID];
        $order = $post[self::POST_ORDER];
        $series = $this->getSeries();

        $tasks = array();
        foreach ($this->getTasks() as $task) {
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

}
