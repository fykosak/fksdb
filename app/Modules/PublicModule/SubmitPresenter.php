<?php

namespace FKSDB\Modules\PublicModule;

use FKSDB\Components\Controls\AjaxSubmit\SubmitContainer;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Grids\SubmitsGrid;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\GoneException;
use FKSDB\Exceptions\ModelException;
use FKSDB\ORM\Models\ModelLogin;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\ModelQuizQuestion;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Models\ModelTask;
use FKSDB\ORM\Services\ServiceQuizQuestion;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\ORM\Services\ServiceSubmitQuizQuestion;
use FKSDB\ORM\Services\ServiceTask;
use FKSDB\ORM\Tables\TypedTableSelection;
use FKSDB\Submits\FileSystemStorage\UploadedStorage;
use FKSDB\Submits\ProcessingException;
use FKSDB\Submits\SubmitHandlerFactory;
use FKSDB\UI\PageTitle;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Http\FileUpload;
use Tracy\Debugger;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class SubmitPresenter extends BasePresenter {

    private ServiceSubmit $submitService;
    private ServiceSubmitQuizQuestion $submitQuizQuestionService;
    private UploadedStorage $uploadedSubmitStorage;
    private ServiceTask $taskService;
    private ServiceQuizQuestion $quizQuestionService;
    private SubmitHandlerFactory $submitHandlerFactory;

    final public function injectTernary(
        ServiceSubmit $submitService,
        ServiceSubmitQuizQuestion $submitQuizQuestionService,
        UploadedStorage $filesystemUploadedSubmitStorage,
        ServiceTask $taskService,
        ServiceQuizQuestion $quizQuestionService,
        SubmitHandlerFactory $submitHandlerFactory
    ): void {
        $this->submitService = $submitService;
        $this->submitQuizQuestionService = $submitQuizQuestionService;
        $this->uploadedSubmitStorage = $filesystemUploadedSubmitStorage;
        $this->taskService = $taskService;
        $this->quizQuestionService = $quizQuestionService;
        $this->submitHandlerFactory = $submitHandlerFactory;
    }

    /* ******************* AUTH ************************/

    public function authorizedDefault(): void {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('submit', 'upload', $this->getSelectedContest()));
    }

    public function authorizedAjax(): void {
        $this->authorizedDefault();
    }

    /* ********************** TITLE **********************/
    public function titleDefault(): void {
        $this->setPageTitle(new PageTitle(_('Submit a solution'), 'fa fa-cloud-upload'));
    }

    public function titleList(): void {
        $this->setPageTitle(new PageTitle(_('Submitted solutions'), 'fa fa-cloud-upload'));
    }

    public function titleAjax(): void {
        $this->titleDefault();
    }

    /**
     * @throws GoneException
     * @deprecated
     */
    public function actionDownload(): void {
        throw new GoneException('');
    }

    public function renderDefault(): void {
        $this->template->hasTasks = count($this->getAvailableTasks()) > 0;
        $this->template->canRegister = false;
        $this->template->hasForward = false;
        if (!$this->template->hasTasks) {
            /** @var ModelPerson $person */
            $person = $this->getUser()->getIdentity()->getPerson();
            $contestants = $person->getActiveContestants($this->yearCalculator);
            $contestant = $contestants[$this->getSelectedContest()->contest_id];
            $currentYear = $this->yearCalculator->getCurrentYear($this->getSelectedContest());
            $this->template->canRegister = ($contestant->year < $currentYear + $this->yearCalculator->getForwardShift($this->getSelectedContest()));

            $this->template->hasForward = ($this->getSelectedYear() == $this->yearCalculator->getCurrentYear($this->getSelectedContest())) && ($this->yearCalculator->getForwardShift($this->getSelectedContest()) > 0);
        }
    }

    public function renderAjax(): void {
        $this->template->availableTasks = $this->getAvailableTasks();
    }

    /**
     * @return FormControl
     * @throws BadTypeException
     */
    protected function createComponentUploadForm(): FormControl {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();

        $taskIds = [];
        /** @var ModelLogin $login */
        $login = $this->getUser()->getIdentity();
        $personHistory = $login->getPerson()->getHistory($this->getSelectedAcademicYear());
        $studyYear = ($personHistory && isset($personHistory->study_year)) ? $personHistory->study_year : null;
        if ($studyYear === null) {
            $this->flashMessage(_('Contestant is missing study year. Not all tasks are thus available.'));
        }
        $prevDeadline = null;
        /** @var ModelTask $task */
        foreach ($this->getAvailableTasks() as $task) {
            $questions = $this->quizQuestionService->getTable()->where('task_id', $task->task_id);

            if ($task->submit_deadline !== $prevDeadline) {
                $form->addGroup(sprintf(_('Deadline %s'), $task->submit_deadline));
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
                    ->addRule(Form::MIME_TYPE, _('Only PDF files are accepted.'), 'application/pdf'); //TODO verify this check at production server

                if (!in_array($studyYear, array_keys($task->getStudyYears()))) {
                    $upload->setOption('description', _('Task is not for your category.'));
                    $upload->setDisabled();
                }

                if ($submit && $this->uploadedSubmitStorage->fileExists($submit)) {
                    $overwrite = $container->addCheckbox('overwrite', _('Overwrite submitted solutions.'));
                    $conditionedUpload->addConditionOn($overwrite, Form::EQUAL, false)->addRule(Form::BLANK, _('Either tick overwrite the solution, or don\'t submit it.'));
                }
            } else {
                //Implementation of quiz questions
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
            $form->addSubmit('upload', _('Submit'));
            $form->onSuccess[] = function (Form $form) {
                $this->handleUploadFormSuccess($form);
            };
            // $form->addProtection(_('The form has expired. Please send it again.'));
        }

        return $control;
    }

    protected function createComponentSubmitsGrid(): SubmitsGrid {
        return new SubmitsGrid($this->getContext(), $this->getContestant());
    }

    protected function createComponentSubmitContainer(): SubmitContainer {
        return new SubmitContainer($this->getContext(), $this->getContestant(), $this->getSelectedContest(), $this->getSelectedAcademicYear(), $this->getSelectedYear());
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     */
    private function handleUploadFormSuccess(Form $form): void {
        $values = $form->getValues();

        Debugger::log(\sprintf('Contestant %d upload %s', $this->getContestant()->ct_id, $values['tasks']), 'old-submit');

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
                    $this->flashMessage(sprintf(_('Task %s cannot be submitted anymore.'), $task->label), self::FLASH_ERROR);
                    continue;
                }
                /** @var FileUpload[] $taskValues */
                $taskValues = $values['task' . $task->task_id];

                if (count($questions)) {
                    /** @var ModelQuizQuestion $question */
                    foreach ($questions as $question) {
                        $name = 'question' . $question->question_id;
                        $answer = $taskValues[$name];
                        if ($answer != null) {
                            $this->submitQuizQuestionService->saveSubmittedQuestion($question, $this->getContestant(), $answer);
                        }
                    }
                    $this->submitHandlerFactory->handleQuizSubmit($task, $this->getContestant());
                } else {
                    if (!isset($taskValues['file'])) { // upload field was disabled
                        continue;
                    }
                    if (!$taskValues['file']->isOk()) {
                        Debugger::log(sprintf("Uploaded file error %s.", $taskValues['file']->getError()), Debugger::WARNING);
                        continue;
                    }
                    $this->submitHandlerFactory->handleSave($taskValues['file'], $task, $this->getContestant());
                }
                $this->flashMessage(sprintf(_('Task %s submitted.'), $task->label), self::FLASH_SUCCESS);
            }

            $this->uploadedSubmitStorage->commit();
            $this->submitService->getConnection()->commit();
            $this->redirect('this');
        } catch (ModelException | ProcessingException $exception) {
            $this->uploadedSubmitStorage->rollback();
            $this->submitService->getConnection()->rollBack();
            Debugger::log($exception);
            $this->flashMessage(_('Task storing error.'), self::FLASH_ERROR);
        }
    }

    private function getAvailableTasks(): TypedTableSelection {
        $tasks = $this->taskService->getTable();
        $tasks->where('contest_id = ? AND year = ?', $this->getSelectedContest()->contest_id, $this->getSelectedYear());
        $tasks->where('submit_start IS NULL OR submit_start < NOW()');
        $tasks->where('submit_deadline IS NULL OR submit_deadline >= NOW()');
        $tasks->order('ISNULL(submit_deadline) ASC, submit_deadline ASC');

        return $tasks;
    }
}
