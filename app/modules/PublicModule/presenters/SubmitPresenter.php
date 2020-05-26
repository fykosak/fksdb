<?php

namespace PublicModule;

use FKSDB\Components\Control\AjaxUpload\AjaxUpload;
use FKSDB\Components\Control\AjaxUpload\SubmitSaveTrait;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Grids\SubmitsGrid;
use FKSDB\Exceptions\GoneException;
use FKSDB\ORM\Models\ModelLogin;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\ModelQuizQuestion;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Models\ModelTask;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\ORM\Services\ServiceTask;
use FKSDB\ORM\Tables\TypedTableSelection;
use FKSDB\Submits\FileSystemStorage\UploadedStorage;
use FKSDB\Submits\ProcessingException;
use FKSDB\Exceptions\ModelException;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Tracy\Debugger;
use FKSDB\ORM\Services\ServiceQuizQuestion;
use FKSDB\ORM\Services\ServiceSubmitQuizQuestion;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class SubmitPresenter extends BasePresenter {
    use SubmitSaveTrait;

    /** @var ServiceSubmit */
    private $submitService;

    /**
     * @param ServiceSubmit $submitService
     * @return void
     */
    public function injectSubmitService(ServiceSubmit $submitService) {
        $this->submitService = $submitService;
    }

    /** @var ServiceSubmitQuizQuestion */
    private $submitQuizQuestionService;

    /**
     * @param ServiceSubmitQuizQuestion $submitQuizQuestionService
     * @return void
     */
    public function injectSubmitQuizQuestionService(ServiceSubmitQuizQuestion $submitQuizQuestionService) {
        $this->submitQuizQuestionService = $submitQuizQuestionService;
    }

    /** @var UploadedStorage */
    private $uploadedSubmitStorage;

    /**
     * @param UploadedStorage $filesystemUploadedSubmitStorage
     * @return void
     */
    public function injectSubmitUploadedStorage(UploadedStorage $filesystemUploadedSubmitStorage) {
        $this->uploadedSubmitStorage = $filesystemUploadedSubmitStorage;
    }

    /** @var ServiceTask */
    private $taskService;

    /**
     * @param ServiceTask $taskService
     * @return void
     */
    public function injectTaskService(ServiceTask $taskService) {
        $this->taskService = $taskService;
    }

    /** @var ServiceQuizQuestion */
    private $quizQuestionService;

    /**
     * @param ServiceQuizQuestion $quizQuestionService
     * @return void
     */
    public function injectQuizQuestionService(ServiceQuizQuestion $quizQuestionService) {
        $this->quizQuestionService = $quizQuestionService;
    }

    /* ******************* AUTH ************************/
    /**
     * @throws BadRequestException
     */
    public function authorizedDefault() {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('submit', 'upload', $this->getSelectedContest()));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedAjax() {
        $this->authorizedDefault();
    }

    /* ********************** TITLE **********************/
    public function titleDefault() {
        $this->setTitle(_('Odevzdat řešení'), 'fa fa-cloud-upload');
    }

    public function titleAjax() {
        return $this->titleDefault();
    }

    /**
     * @throws BadRequestException
     * @deprecated
     */
    public function actionDownload() {
        throw new GoneException('');
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
            $contestants = $person->getActiveContestants($this->getYearCalculator());
            $contestant = $contestants[$this->getSelectedContest()->contest_id];
            $currentYear = $this->getYearCalculator()->getCurrentYear($this->getSelectedContest());
            $this->template->canRegister = ($contestant->year < $currentYear + $this->getYearCalculator()->getForwardShift($this->getSelectedContest()));

            $this->template->hasForward = ($this->getSelectedYear() == $this->getYearCalculator()->getCurrentYear($this->getSelectedContest())) && ($this->getYearCalculator()->getForwardShift($this->getSelectedContest()) > 0);
        }
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
        /** @var ModelLogin $login */
        $login = $this->getUser()->getIdentity();
        $personHistory = $login->getPerson()->getHistory($this->getSelectedAcademicYear());
        $studyYear = ($personHistory && isset($personHistory->study_year)) ? $personHistory->study_year : null;
        if ($studyYear === null) {
            $this->flashMessage(_('Řešitel nemá vyplněn ročník, nebudou dostupné všechny úlohy.'));
        }
        /** @var ModelTask $task */
        foreach ($this->getAvailableTasks() as $task) {
            $questions = $this->quizQuestionService->getTable();
            $questions->where('task_id', $task->task_id);

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
            if (!count($questions)) {
                $upload = $container->addUpload('file', $task->getFQName());
                $conditionedUpload = $upload
                    ->addCondition(Form::FILLED)
                    ->addRule(Form::MIME_TYPE, _('Lze nahrávat pouze PDF soubory.'), 'application/pdf'); //TODO verify this check at production server

                if (!in_array($studyYear, array_keys($task->getStudyYears()))) {
                    $upload->setOption('description', _('Úloha není určena pro Tvou kategorii.'));
                    $upload->setDisabled();
                }

                if ($submit && $this->uploadedSubmitStorage->fileExists($submit)) {
                    $overwrite = $container->addCheckbox('overwrite', _('Přepsat odeslané řešení.'));
                    $conditionedUpload->addConditionOn($overwrite, Form::EQUAL, false)->addRule(~Form::FILLED, _('Buď zvolte přepsání odeslaného řešení anebo jej neposílejte.'));
                }
            } else {
                //Implementaton of quiz questions
                $options = ['A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D']; //TODO add variability of options
                foreach ($questions as $question) {
                    $select = $container->addRadioList('question' . $question->question_id, $task->getFQName() . ' - ' . $question->getFQName(), $options);
                    foreach ($options as $option) {
                        $select->setValue($option);
                    }

                    $existingEntry = $this->submitQuizQuestionService->findByContestant($this->getContestant()->ct_id, $question->question_id);
                    if ($existingEntry) {
                        $existingAnswer = $existingEntry->answer;
                        $select->setValue($existingAnswer);
                    } else {
                        $select->setValue(null);
                    }
                }
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

    public function createComponentAjaxUpload(): AjaxUpload {
        return new AjaxUpload($this->getContext());
    }

    /**
     * @return SubmitsGrid
     * @throws BadRequestException
     */
    public function createComponentSubmitsGrid(): SubmitsGrid {
        return new SubmitsGrid($this->getContext(), $this->getContestant());
    }

    /**
     * @param Form $form
     * @throws BadRequestException
     * @throws AbortException
     * @throws \Exception
     * @internal
     */
    public function handleUploadFormSuccess(Form $form) {
        $values = $form->getValues();

        $taskIds = explode(',', $values['tasks']);
        $validIds = $this->getAvailableTasks()->fetchPairs('task_id', 'task_id');

        try {
            $this->submitService->getConnection()->beginTransaction();
            $this->uploadedSubmitStorage->beginTransaction();

            foreach ($taskIds as $taskId) {

                $questions = $this->quizQuestionService->getTable()->where('task_id', $taskId);
                /** @var ModelTask $task */
                $task = $this->taskService->findByPrimary($taskId);

                if (!isset($validIds[$taskId])) {
                    $this->flashMessage(sprintf(_('Úlohu %s již není možno odevzdávat.'), $task->label), self::FLASH_ERROR);
                    continue;
                }

                $taskValues = $values['task' . $task->task_id];

                //Implemetation of quiz questions
                /** @var ModelQuizQuestion $question */
                foreach ($questions as $question) {
                    $name = 'question' . $question->question_id;
                    $answer = $taskValues[$name];
                    $this->submitQuizQuestionService->saveSubmitedQuestion($question, $this->getContestant(), $answer);
                }

                if (!isset($taskValues['file'])) { // upload field was disabled
                    continue;
                }
                if (!$taskValues['file']->isOk()) {
                    Debugger::log(sprintf("Uploaded file error %s.", $taskValues['file']->getError()), Debugger::WARNING);
                    continue;
                }

                $this->traitSaveSubmit($taskValues['file'], $task, $this->getContestant());

                $this->flashMessage(sprintf(_('Úloha %s odevzdána.'), $task->label), self::FLASH_SUCCESS);
            }

            $this->uploadedSubmitStorage->commit();
            $this->submitService->getConnection()->commit();
            $this->redirect('this');
        } catch (ModelException $exception) {
            $this->uploadedSubmitStorage->rollback();
            $this->submitService->getConnection()->rollBack();

            Debugger::log($exception);
            $this->flashMessage(_('Došlo k chybě při ukládání úloh.'), self::FLASH_ERROR);
        } catch (ProcessingException $exception) {
            $this->uploadedSubmitStorage->rollback();
            $this->submitService->getConnection()->rollBack();

            Debugger::log($exception);
            $this->flashMessage(_('Došlo k chybě při ukládání úloh.'), self::FLASH_ERROR);
        }
    }

    /**
     * @return TypedTableSelection
     * @throws BadRequestException
     */
    public function getAvailableTasks(): TypedTableSelection {
        $tasks = $this->taskService->getTable();
        $tasks->where('contest_id = ? AND year = ?', $this->getSelectedContest()->contest_id, $this->getSelectedYear());
        $tasks->where('submit_start IS NULL OR submit_start < NOW()');
        $tasks->where('submit_deadline IS NULL OR submit_deadline >= NOW()');
        $tasks->order('ISNULL(submit_deadline) ASC, submit_deadline ASC');

        return $tasks;
    }

    /**
     * @param int $taskId
     * @return ModelTask|null
     *
     * @throws BadRequestException
     */
    public function isAvailableSubmit($taskId) {
        /** @var ModelTask $task */
        foreach ($this->getAvailableTasks() as $task) {
            if ($task->task_id == $taskId) {
                return $task;
            }
        }
        return null;
    }

    protected function getUploadedStorage(): UploadedStorage {
        return $this->getContext()->getByType(UploadedStorage::class);
    }

    protected function getServiceSubmit(): ServiceSubmit {
        return $this->submitService;
    }
}
