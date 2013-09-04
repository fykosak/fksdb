<?php

namespace PublicModule;

use ModelException;
use ModelSubmit;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\DateTime;
use Nette\Diagnostics\Debugger;
use ServiceSubmit;
use ServiceTask;
use Tasks\SubmitStorage;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class SubmitPresenter extends BasePresenter {

    /** @var ServiceTask */
    private $taskService;

    /** @var ServiceSubmit */
    private $submitService;

    /**
     * @var SubmitStorage
     */
    private $submitStorage;

    public function injectTaskService(ServiceTask $taskService) {
        $this->taskService = $taskService;
    }

    public function injectSubmitService(ServiceSubmit $submitService) {
        $this->submitService = $submitService;
    }

    public function injectSubmitStorage(SubmitStorage $submitStorage) {
        $this->submitStorage = $submitStorage;
    }

    public function actionDefault() {
        if (!$this->contestAuthorizator->isAllowed('submit', 'upload', $this->getSelectedContest())) {
            throw new BadRequestException('Nedostatečné oprávnění.', 403);
        }
    }

    public function createComponentUploadForm($name) {
        $form = new Form();

        $prevDeadline = null;
        $taskIds = array();

        foreach ($this->getAvailableTasks() as $task) {
            if ($task->submit_deadline != $prevDeadline) {
                $form->addGroup(sprintf('Termín %s', $task->submit_deadline));
            }

            $container = $form->addContainer('task' . $task->task_id);
            $upload = $container->addUpload('file', $task->label)
                    ->setOption('description', $task->name_cs)//TODO i18n
                    ->addCondition(Form::FILLED)
                    ->addRule(Form::MIME_TYPE, 'Lze nahrávat pouze PDF soubory.', 'application/pdf'); //TODO verify this check at server

            $submit = $this->submitService->findByContestant($this->getContestant()->ct_id, $task->task_id);
            if ($submit && $this->submitStorage->existsFile($submit)) {
                $overwrite = $container->addCheckbox('overwrite', 'Přepsat odeslané řešení.');
                $upload->addConditionOn($overwrite, Form::EQUAL, false)->addRule(~Form::FILLED, 'Buď zvolte přepsání odeslaného řešení anebo jej neposílejte.');
            }


            $prevDeadline = $task->submit_deadline;
            $taskIds[] = $task->task_id;
        }

        $form->addHidden('tasks', implode(',', $taskIds));

        $form->setCurrentGroup();
        $form->addSubmit('upload', 'Odeslat');
        $form->onSuccess[] = array($this, 'handleUploadFormSuccess');

        $form->addProtection('Vypršela časová platnost formuláře. Odešlete jej prosím znovu.');

        return $form;
    }

    public function handleUploadFormSuccess($form) {
        $values = $form->getValues();

        $ctId = $this->getContestant()->ct_id;
        $taskIds = explode(',', $values['tasks']);
        $validIds = $this->getAvailableTasks()->fetchPairs('task_id', 'task_id');

        try {
            $this->submitService->getConnection()->beginTransaction();
            $this->submitStorage->beginTransaction();

            foreach ($taskIds as $taskId) {
                $task = $this->taskService->findByPrimary($taskId);

                if (!isset($validIds[$taskId])) {
                    $this->flashMessage(sprintf('Úlohu %s již není možno odevzdávat.', $task->label), 'error');
                    continue;
                }

                $taskValues = $values['task' . $task->task_id];

                if (!$taskValues['file']->isOk()) {
                    continue;
                }

                // store submit
                $submit = $this->submitService->findByContestant($ctId, $task->task_id);
                if (!$submit) {
                    $submit = $this->submitService->createNew(array(
                        'task_id' => $task->task_id,
                        'ct_id' => $ctId,
                    ));
                }
                //TODO handle cases when user modifies already graded submit (i.e. with bad timings)
                $submit->submitted_on = new DateTime();
                $submit->source = ModelSubmit::SOURCE_UPLOAD;
                $submit->ct_id; // stupid... touch the field in order to have it loaded via ActiveRow

                $this->submitService->save($submit);

                // store file
                $this->submitStorage->storeFile($taskValues['file'], $submit);

                $this->flashMessage(sprintf('Úloha %s odevzdána.', $task->label));
            }

            $this->submitStorage->commit();
            $this->submitService->getConnection()->commit();
            $this->redirect('this');
        } catch (ModelException $e) {
            $this->submitStorage->rollback();
            $this->submitService->getConnection()->rollBack();

            Debugger::log($e);
            $this->flashMessage('Došlo k chybě při ukládání úloh.', 'error');
        } catch (SubmitStorageException $e) {
            $this->submitStorage->rollback();
            $this->submitService->getConnection()->rollBack();

            Debugger::log($e);
            $this->flashMessage('Došlo k chybě při ukládání úloh.', 'error');
        }
    }

    private function getAvailableTasks() {
        $tasks = $this->taskService->getTable();
        $tasks->where('contest_id = ? AND year = ?', $this->getSelectedContest()->contest_id, $this->getSelectedYear());
        $tasks->where('submit_start IS NULL OR submit_start < NOW()');
        $tasks->where('submit_deadline IS NULL OR submit_deadline >= NOW()');
        $tasks->order('ISNULL(submit_deadline) ASC, submit_deadline ASC');

        return $tasks;
    }

}
