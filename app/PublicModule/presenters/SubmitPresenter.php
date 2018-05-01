<?php

namespace PublicModule;

use FKSDB\Components\Grids\SubmitsGrid;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use ModelException;
use ModelSubmit;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\FileResponse;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\Form;
use Nette\DateTime;
use Nette\Diagnostics\Debugger;
use Nette\Diagnostics\FireLogger;
use ServiceSubmit;
use ServiceTask;
use Submits\ISubmitStorage;
use Submits\ProcessingException;

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
     * @var ISubmitStorage
     */
    private $submitStorage;

    public function injectTaskService(ServiceTask $taskService) {
        $this->taskService = $taskService;
    }

    public function injectSubmitService(ServiceSubmit $submitService) {
        $this->submitService = $submitService;
    }

    public function injectSubmitStorage(ISubmitStorage $submitStorage) {
        $this->submitStorage = $submitStorage;
    }

    public function authorizedDefault() {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('submit', 'upload', $this->getSelectedContest()));
    }

    public function titleDefault() {
        $this->setTitle(_('Odevzdat řešení'));
    }

    /**
     * @param $id
     * @throws BadRequestException
     */
    public function authorizedDownload($id) {
        /**
         * @var $submit ModelSubmit
         */
        $submit = $this->submitService->findByPrimary($id);

        if (!$submit) {
            throw new BadRequestException('Neexistující submit.', 404);
        }

        $submit->task_id; // stupid touch
        $contest = $submit->getContestant()->getContest();
        $this->setAuthorized($this->contestAuthorizator->isAllowed($submit, 'download', $contest));

        if ($submit->source != ModelSubmit::SOURCE_UPLOAD) {
            throw new BadRequestException('Lze stahovat jen uploadovaná řešení.', 501);
        }
    }

    /**
     * @throws BadRequestException
     */
    public function renderDefault() {
        $this->template->hasTasks = count($this->getAvailableTasks()) > 0;
        $this->template->canRegister = false;
        $this->template->hasForward = false;
        if (!$this->template->hasTasks) {
            $person = $this->getUser()->getIdentity()->getPerson();
            $contestants = $person->getActiveContestants($this->yearCalculator);
            $contestant = $contestants[$this->getSelectedContest()->contest_id];
            $currentYear = $this->getYearCalculator()->getCurrentYear($this->getSelectedContest());
            $this->template->canRegister = ($contestant->year < $currentYear + $this->getYearCalculator()->getForwardShift($this->getSelectedContest()));

            $this->template->hasForward = ($this->getSelectedYear() == $this->getYearCalculator()->getCurrentYear($this->getSelectedContest())) && ($this->getYearCalculator()->getForwardShift($this->getSelectedContest()) > 0);
        }
    }

    /**
     * @param $taskId integer
     * @return \ModelTask
     *
     */
    private function isAvailableSubmit($taskId) {
        /**
         * @var $task \ModelTask
         */
        foreach ($this->getAvailableTasks() as $task) {
            if ($task->task_id == $taskId) {
                return $task;
            };
        }
        return null;
    }

    /**
     * @param \Nette\Http\FileUpload $file
     * @param $contestant \ModelContestant
     * @param $task \ModelTask
     * @return \AbstractModelSingle|ModelSubmit
     */
    private function saveSubmit(\Nette\Http\FileUpload $file, \ModelTask $task, \ModelContestant $contestant) {
        $submit = $this->submitService->findByContestant($contestant->ct_id, $task->task_id);
        if (!$submit) {
            $submit = $this->submitService->createNew([
                'task_id' => $task->task_id,
                'ct_id' => $contestant->ct_id,
            ]);
        }
        //TODO handle cases when user modifies already graded submit (i.e. with bad timings)
        $submit->submitted_on = new DateTime();
        $submit->source = ModelSubmit::SOURCE_UPLOAD;
        $submit->ct_id; // stupid... touch the field in order to have it loaded via ActiveRow

        $this->submitService->save($submit);

        // store file
        $this->submitStorage->storeFile($file->getTemporaryFile(), $submit);
        return $submit;

    }

    /**
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    public function renderAjax() {
        if ($this->isAjax()) {
            $this->getHttpResponse()->setContentType('Content-Type: text/html; charset=utf-8');
            $contestant = $this->getContestant();
            $files = $this->getHttpRequest()->getFiles();

            foreach ($files as $name => $fileContainer) {
                $this->submitService->getConnection()->beginTransaction();
                $this->submitStorage->beginTransaction();
                if (!preg_match('/task([0-9]+)/', $name, $matches)) {
                    continue;
                }

                $task = $this->isAvailableSubmit($matches[1]);
                if (!$task) {
                    $this->getHttpResponse()->setCode('403');
                    $this->sendResponse(new JsonResponse(['error' => 'upload not allowed']));
                };

                FireLogger::log($fileContainer);
                /**
                 * @var $file \Nette\Http\FileUpload
                 */

                // $file = $fileContainer['file'];

                $file = $fileContainer;
                if (!$file->isOk()) {
                    $this->getHttpResponse()->setCode('500');
                    $this->sendResponse(new JsonResponse(['error' => 'file is not Ok']));
                    return;
                }

                // store submit
                $submit = $this->saveSubmit($file, $task, $contestant);

                $this->submitStorage->commit();
                $this->submitService->getConnection()->commit();
                $this->sendResponse(new JsonResponse(
                    [
                        'msg' => 'success',
                        'do' => 'upload',
                        'data' => $this->serializeData($submit, $task),
                    ]));
            }
            die();
        }
        $this->renderDefault();

        $data = [];
        /**
         * @var $task \ModelTask
         */
        foreach ($this->getAvailableTasks() as $task) {
            $submit = $this->submitService->findByContestant($this->getContestant()->ct_id, $task->task_id);

            $data[$task->task_id] = $this->serializeData($submit, $task);
        };
        $this->template->uploadData = json_encode($data);
    }

    /**
     * @param ModelSubmit $submit
     * @param \ModelTask $task
     * @return array
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    private function serializeData($submit, \ModelTask $task) {
        return [
            'submitId' => $submit ? $submit->submit_id : null,
            'name' => $task->getFQName(),
            'href' => $submit ? $this->link('download', ['id' => $submit->submit_id]) : null,
            'taskId' => $task->task_id,
            'deadline' => sprintf(_('Termín %s'), $task->submit_deadline),
        ];
    }

    /**
     * @param $id
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function actionDownload($id) {
        /**
         * @var $submit ModelSubmit
         */
        $submit = $this->submitService->findByPrimary($id);

        $filename = $this->submitStorage->retrieveFile($submit);
        if (!$filename) {
            throw new BadRequestException('Poškozený soubor submitu', 500);
        }

        //TODO better construct user's filename and PDF type dependency
        $response = new FileResponse($filename, $submit->getTask()->getFQName() . '.pdf', 'application/pdf');
        $this->sendResponse($response);
    }

    public function createComponentUploadForm($name) {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());

        $prevDeadline = null;
        $taskIds = array();
        $personHistory = $this->getUser()->getIdentity()->getPerson()->getHistory($this->getSelectedAcademicYear());
        $studyYear = ($personHistory && isset($personHistory->study_year)) ? $personHistory->study_year : null;
        if ($studyYear === null) {
            $this->flashMessage(_('Řešitel nemá vyplněn ročník, nebudou dostupné všechny úlohy.'));
        }

        foreach ($this->getAvailableTasks() as $task) {
            if ($task->submit_deadline != $prevDeadline) {
                $form->addGroup(sprintf(_('Termín %s'), $task->submit_deadline));
            }
            $submit = $this->submitService->findByContestant($this->getContestant()->ct_id, $task->task_id);
            if ($submit && $submit->source == ModelSubmit::SOURCE_POST) {
                continue; // prevDeadline will work though
            }

            $container = $form->addContainer('task' . $task->task_id);
            $upload = $container->addUpload('file', $task->getFQName());
            $conditionedUpload = $upload
                ->addCondition(Form::FILLED)
                ->addRule(Form::MIME_TYPE, _('Lze nahrávat pouze PDF soubory.'), 'application/pdf'); //TODO verify this check at production server

            if (!in_array($studyYear, array_keys($task->getStudyYears()))) {
                $upload->setOption('description', _('Úloha není určena pro Tvou kategorii.'));
                $upload->setDisabled();
            }

            if ($submit && $this->submitStorage->existsFile($submit)) {
                $overwrite = $container->addCheckbox('overwrite', _('Přepsat odeslané řešení.'));
                $conditionedUpload->addConditionOn($overwrite, Form::EQUAL, false)->addRule(~Form::FILLED, _('Buď zvolte přepsání odeslaného řešení anebo jej neposílejte.'));
            }


            $prevDeadline = $task->submit_deadline;
            $taskIds[] = $task->task_id;
        }

        if ($taskIds) {
            $form->addHidden('tasks', implode(',', $taskIds));

            $form->setCurrentGroup();
            $form->addSubmit('upload', _('Odeslat'));
            $form->onSuccess[] = array($this, 'handleUploadFormSuccess');

            $form->addProtection(_('Vypršela časová platnost formuláře. Odešlete jej prosím znovu.'));
        }

        return $form;
    }

    public function createComponentSubmitsGrid($name) {
        $grid = new SubmitsGrid($this->submitService, $this->submitStorage, $this->getContestant());

        return $grid;
    }

    /**
     * @param $form Form
     * @throws \Nette\Application\AbortException
     */
    public function handleUploadFormSuccess(Form $form) {
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
                    $this->flashMessage(sprintf(_('Úlohu %s již není možno odevzdávat.'), $task->label), self::FLASH_ERROR);
                    continue;
                }

                $taskValues = $values['task' . $task->task_id];

                if (!isset($taskValues['file'])) { // upload field was disabled
                    continue;
                }
                if (!$taskValues['file']->isOk()) {
                    Debugger::log(sprintf("Uploaded file error %s.", $taskValues['file']->getError()), Debugger::WARNING);
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
                $this->submitStorage->storeFile($taskValues['file']->getTemporaryFile(), $submit);

                $this->flashMessage(sprintf(_('Úloha %s odevzdána.'), $task->label), self::FLASH_SUCCESS);
            }

            $this->submitStorage->commit();
            $this->submitService->getConnection()->commit();
            $this->redirect('this');
        } catch (ModelException $e) {
            $this->submitStorage->rollback();
            $this->submitService->getConnection()->rollBack();

            Debugger::log($e);
            $this->flashMessage(_('Došlo k chybě při ukládání úloh.'), self::FLASH_ERROR);
        } catch (ProcessingException $e) {
            $this->submitStorage->rollback();
            $this->submitService->getConnection()->rollBack();

            Debugger::log($e);
            $this->flashMessage(_('Došlo k chybě při ukládání úloh.'), self::FLASH_ERROR);
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
