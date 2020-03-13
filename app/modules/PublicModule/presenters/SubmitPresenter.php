<?php

namespace PublicModule;

use FKSDB\Components\Control\AjaxUpload\AjaxUpload;
use FKSDB\Components\Control\AjaxUpload\SubmitSaveTrait;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Grids\SubmitsGrid;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Models\ModelTask;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\ORM\Services\ServiceTask;
use FKSDB\Submits\FilesystemCorrectedSubmitStorage;
use FKSDB\Submits\FilesystemSubmitUploadedStorage;
use FKSDB\Submits\ISubmitStorage;
use FKSDB\Submits\ProcessingException;
use ModelException;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\FileResponse;
use Nette\Application\UI\Form;
use Nette\Database\Table\Selection;
use Tracy\Debugger;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class SubmitPresenter extends BasePresenter {
    use SubmitSaveTrait;

    /** @var ServiceTask */
    private $taskService;

    /** @var ServiceSubmit */
    private $submitService;

    /**
     * @var FilesystemSubmitUploadedStorage
     */
    private $filesystemSubmitUploadedStorage;
    /**
     * @var FilesystemCorrectedSubmitStorage
     */
    private $filesystemCorrectedSubmitStorage;

    /**
     * @param ServiceTask $taskService
     */
    public function injectTaskService(ServiceTask $taskService) {
        $this->taskService = $taskService;
    }

    /**
     * @param ServiceSubmit $submitService
     */
    public function injectSubmitService(ServiceSubmit $submitService) {
        $this->submitService = $submitService;
    }

    /**
     * @param FilesystemSubmitUploadedStorage $filesystemSubmitUploadedStorage
     */
    public function injectSubmitUploadedStorage(FilesystemSubmitUploadedStorage $filesystemSubmitUploadedStorage) {
        $this->filesystemSubmitUploadedStorage = $filesystemSubmitUploadedStorage;
    }

    /**
     * @param FilesystemCorrectedSubmitStorage $filesystemCorrectedSubmitStorage
     */
    public function injectSubmitCorrectedStorage(FilesystemCorrectedSubmitStorage $filesystemCorrectedSubmitStorage) {
        $this->filesystemCorrectedSubmitStorage = $filesystemCorrectedSubmitStorage;
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedDefault() {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('submit', 'upload', $this->getSelectedContest()));
    }

    public function titleDefault() {
        $this->setTitle(_('Odevzdat řešení'));
        $this->setIcon('fa fa-cloud-upload');
    }

    /**
     * @param $id
     * @throws BadRequestException
     */
    public function authorizedDownload(int $id) {
        /**
         * @var ModelSubmit $submit
         */
        $submit = $this->submitService->findByPrimary($id);

        if (!$submit) {
            throw new BadRequestException('Neexistující submit.', 404);
        }
        $this->setAuthorized($this->contestAuthorizator->isAllowedForContestReferencedModel($submit->getContestant(), 'download'));

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
            /**
             * @var ModelPerson $person
             */
            $person = $this->getUser()->getIdentity()->getPerson();
            $contestants = $person->getActiveContestants($this->yearCalculator);
            $contestant = $contestants[$this->getSelectedContest()->contest_id];
            $currentYear = $this->getYearCalculator()->getCurrentYear($this->getSelectedContest());
            $this->template->canRegister = ($contestant->year < $currentYear + $this->getYearCalculator()->getForwardShift($this->getSelectedContest()));

            $this->template->hasForward = ($this->getSelectedYear() == $this->getYearCalculator()->getCurrentYear($this->getSelectedContest())) && ($this->getYearCalculator()->getForwardShift($this->getSelectedContest()) > 0);
        }
    }


    /**
     * @param $id
     * @throws BadRequestException
     * @throws AbortException
     */
    public function actionDownload($id) {
        /**
         * @var ModelSubmit $submit
         */
        $submit = $this->submitService->findByPrimary2($id);

        $filename = $this->submitStorage->retrieveFile($submit);
        if (!$filename) {
            throw new BadRequestException('Poškozený soubor submitu', 500);
        }

        //TODO better construct user's filename and PDF type dependency
        $response = new FileResponse($filename, $submit->getTask()->getFQName() . '.pdf', 'application/pdf');
        $this->sendResponse($response);
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     */
    public function createComponentUploadForm() {
        $control = new FormControl();
        $form = $control->getForm();

        $prevDeadline = null;
        $taskIds = [];
        $personHistory = $this->getUser()->getIdentity()->getPerson()->getHistory($this->getSelectedAcademicYear());
        $studyYear = ($personHistory && isset($personHistory->study_year)) ? $personHistory->study_year : null;
        if ($studyYear === null) {
            $this->flashMessage(_('Řešitel nemá vyplněn ročník, nebudou dostupné všechny úlohy.'));
        }
        /**
         * @var ModelTask $task
         */
        foreach ($this->getAvailableTasks() as $task) {
            if ($task->submit_deadline != $prevDeadline) {
                $form->addGroup(sprintf(_('Termín %s'), $task->submit_deadline));
            }
            $submit = $this->submitService->findByContestant($this->getContestant()->ct_id, $task->task_id);
            if ($submit && $submit->source == ModelSubmit::SOURCE_POST) {
                continue; // prevDeadline will work though
            }
            $container = new ModelContainer();
            $form->addComponent($container, 'task' . $task->task_id);
            //$container = $form->addContainer();
            $upload = $container->addUpload('file', $task->getFQName());
            $conditionedUpload = $upload
                ->addCondition(Form::FILLED)
                ->addRule(Form::MIME_TYPE, _('Lze nahrávat pouze PDF soubory.'), 'application/pdf'); //TODO verify this check at production server

            if (!in_array($studyYear, array_keys($task->getStudyYears()))) {
                $upload->setOption('description', _('Úloha není určena pro Tvou kategorii.'));
                $upload->setDisabled();
            }

            if ($submit && $this->filesystemSubmitUploadedStorage->fileExists($submit)) {
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
            $form->onSuccess[] = function (Form $form) {
                $this->handleUploadFormSuccess($form);
            };

            $form->addProtection(_('Vypršela časová platnost formuláře. Odešlete jej prosím znovu.'));
        }

        return $control;
    }

    /**
     * @return AjaxUpload
     */
    public function createComponentAjaxUpload(): AjaxUpload {
        return new AjaxUpload($this->context, $this->submitService, $this->filesystemSubmitUploadedStorage);
    }

    /**
     * @return SubmitsGrid
     * @throws BadRequestException
     */
    public function createComponentSubmitsGrid(): SubmitsGrid {
        return new SubmitsGrid(
            $this->submitService,
            $this->filesystemSubmitUploadedStorage,
            $this->getContestant(),
            $this->filesystemCorrectedSubmitStorage
        );
    }

    /**
     * @param mixed $form
     * @throws BadRequestException
     * @throws AbortException
     * @throws \Exception
     * @internal
     */
    public function handleUploadFormSuccess($form) {
        $values = $form->getValues();

        $taskIds = explode(',', $values['tasks']);
        $validIds = $this->getAvailableTasks()->fetchPairs('task_id', 'task_id');

        try {
            $this->submitService->getConnection()->beginTransaction();
            $this->filesystemSubmitUploadedStorage->beginTransaction();

            foreach ($taskIds as $taskId) {
                $taskRow = $this->taskService->findByPrimary($taskId);
                $task = ModelTask::createFromActiveRow($taskRow);

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

                $this->saveSubmitTrait($taskValues['file'], $task, $this->getContestant());

                $this->flashMessage(sprintf(_('Úloha %s odevzdána.'), $task->label), self::FLASH_SUCCESS);
            }

            $this->filesystemSubmitUploadedStorage->commit();
            $this->submitService->getConnection()->commit();
            $this->redirect('this');
        } catch (ModelException $exception) {
            $this->filesystemSubmitUploadedStorage->rollback();
            $this->submitService->getConnection()->rollBack();

            Debugger::log($exception);
            $this->flashMessage(_('Došlo k chybě při ukládání úloh.'), self::FLASH_ERROR);
        } catch (ProcessingException $exception) {
            $this->filesystemSubmitUploadedStorage->rollback();
            $this->submitService->getConnection()->rollBack();

            Debugger::log($exception);
            $this->flashMessage(_('Došlo k chybě při ukládání úloh.'), self::FLASH_ERROR);
        }
    }

    /**
     * @return Selection
     * @throws BadRequestException
     */
    public function getAvailableTasks() {
        $tasks = $this->taskService->getTable();
        $tasks->where('contest_id = ? AND year = ?', $this->getSelectedContest()->contest_id, $this->getSelectedYear());
        $tasks->where('submit_start IS NULL OR submit_start < NOW()');
        $tasks->where('submit_deadline IS NULL OR submit_deadline >= NOW()');
        $tasks->order('ISNULL(submit_deadline) ASC, submit_deadline ASC');

        return $tasks;
    }

    /**
     * @param integer $taskId
     * @return ModelTask|null
     *
     * @throws BadRequestException
     */
    public function isAvailableSubmit($taskId) {
        /**
         * @var ModelTask $task
         */
        foreach ($this->getAvailableTasks() as $task) {
            if ($task->task_id == $taskId) {
                return $task;
            };
        }
        return null;
    }

    public function titleAjax() {
        return $this->titleDefault();
    }

    /**
     * @return ServiceSubmit
     */
    protected function getServiceSubmit(): ServiceSubmit {
        return $this->submitService;
    }

    /**
     * @return ISubmitStorage
     */
    protected function getSubmitStorage(): ISubmitStorage {
        return $this->filesystemSubmitUploadedStorage;
    }
}
