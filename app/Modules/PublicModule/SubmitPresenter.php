<?php

declare(strict_types=1);

namespace FKSDB\Modules\PublicModule;

use FKSDB\Components\Controls\AjaxSubmit\SubmitContainer;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Grids\SubmitsGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Models\ModelQuiz;
use FKSDB\Models\ORM\Models\ModelSubmit;
use FKSDB\Models\ORM\Models\ModelTask;
use FKSDB\Models\ORM\Services\ServiceQuiz;
use FKSDB\Models\ORM\Services\ServiceSubmit;
use FKSDB\Models\ORM\Services\ServiceSubmitQuiz;
use FKSDB\Models\ORM\Services\ServiceTask;
use FKSDB\Models\Submits\FileSystemStorage\UploadedStorage;
use FKSDB\Models\Submits\ProcessingException;
use FKSDB\Models\Submits\StorageException;
use FKSDB\Models\Submits\SubmitHandlerFactory;
use Fykosak\Utils\UI\PageTitle;
use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\NetteORM\TypedTableSelection;
use Nette\Application\UI\Form;
use Nette\Http\FileUpload;
use Tracy\Debugger;

class SubmitPresenter extends BasePresenter
{

    private ServiceSubmit $submitService;
    private ServiceSubmitQuiz $submitQuizQuestionService;
    private UploadedStorage $uploadedSubmitStorage;
    private ServiceTask $taskService;
    private ServiceQuiz $quizQuestionService;
    private SubmitHandlerFactory $submitHandlerFactory;

    final public function injectTernary(
        ServiceSubmit $submitService,
        ServiceSubmitQuiz $submitQuizQuestionService,
        UploadedStorage $filesystemUploadedSubmitStorage,
        ServiceTask $taskService,
        ServiceQuiz $quizQuestionService,
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

    public function authorizedAjax(): void
    {
        $this->authorizedDefault();
    }

    public function authorizedDefault(): void
    {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('submit', 'upload', $this->getSelectedContest()));
    }

    /* ********************** TITLE **********************/

    public function titleList(): PageTitle
    {
        return new PageTitle(_('Submitted solutions'), 'fas fa-cloud-upload-alt');
    }

    public function titleAjax(): PageTitle
    {
        return $this->titleDefault();
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(_('Submit a solution'), 'fas fa-cloud-upload-alt');
    }

    final public function renderDefault(): void
    {
        $this->template->hasTasks = count($this->getAvailableTasks()) > 0;
        $this->template->canRegister = false;
        $this->template->hasForward = false;
        if (!$this->template->hasTasks) {
            /** @var ModelPerson $person */
            $person = $this->getUser()->getIdentity()->getPerson();
            $contestants = $person->getActiveContestants();
            $contestant = $contestants[$this->getSelectedContest()->contest_id];
            $currentContestYear = $this->getSelectedContest()->getCurrentContestYear();
            $this->template->canRegister = ($contestant->year < $currentContestYear->year
                + $this->yearCalculator->getForwardShift($this->getSelectedContest()));

            $this->template->hasForward = ($this->getSelectedContestYear()->year == $currentContestYear->year)
                && ($this->yearCalculator->getForwardShift($this->getSelectedContest()) > 0);
        }
    }

    private function getAvailableTasks(): TypedTableSelection
    {
        // TODO related
        $tasks = $this->taskService->getTable();
        $tasks->where(
            'contest_id = ? AND year = ?',
            $this->getSelectedContestYear()->contest_id,
            $this->getSelectedContestYear()->year
        );
        $tasks->where('submit_start IS NULL OR submit_start < NOW()');
        $tasks->where('submit_deadline IS NULL OR submit_deadline >= NOW()');
        $tasks->order('ISNULL(submit_deadline) ASC, submit_deadline ASC');

        return $tasks;
    }

    final public function renderAjax(): void
    {
        $this->template->availableTasks = $this->getAvailableTasks();
    }

    /**
     * @throws BadTypeException
     */
    protected function createComponentUploadForm(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();

        $taskIds = [];
        /** @var ModelLogin $login */
        $login = $this->getUser()->getIdentity();
        $personHistory = $login->getPerson()->getHistoryByContestYear($this->getSelectedContestYear());
        $studyYear = ($personHistory && isset($personHistory->study_year)) ? $personHistory->study_year : null;
        if ($studyYear === null) {
            $this->flashMessage(_('Contestant is missing study year. Not all tasks are thus available.'));
        }
        $prevDeadline = null;
        /** @var ModelTask $task */
        foreach ($this->getAvailableTasks() as $task) {
            if ($task->submit_deadline !== $prevDeadline) {
                $form->addGroup(sprintf(_('Deadline %s'), $task->submit_deadline));
            }
            $submit = $this->submitService->findByContestant($this->getContestant(), $task);
            if ($submit && $submit->source == ModelSubmit::SOURCE_POST) {
                continue; // prevDeadline will work though
            }
            $container = new ModelContainer();
            $form->addComponent($container, 'task' . $task->task_id);
            //$container = $form->addContainer();
            $questions = $task->related(DbNames::TAB_QUIZ);
            if (!count($questions)) {
                $upload = $container->addUpload('file', $task->getFQName());
                $conditionedUpload = $upload
                    ->addCondition(Form::FILLED)
                    ->addRule(
                        Form::MIME_TYPE,
                        _('Only PDF files are accepted.'),
                        'application/pdf'
                    ); //TODO verify this check at production server

                if (!in_array($studyYear, array_keys($task->getStudyYears()))) {
                    $upload->setOption('description', _('Task is not for your category.'));
                    $upload->setDisabled();
                }

                if ($submit && $this->uploadedSubmitStorage->fileExists($submit)) {
                    $overwrite = $container->addCheckbox('overwrite', _('Overwrite submitted solutions.'));
                    $conditionedUpload->addConditionOn($overwrite, Form::EQUAL, false)->addRule(
                        Form::BLANK,
                        _('Either tick overwrite the solution, or don\'t submit it.')
                    );
                }
            } else {
                //Implementation of quiz questions
                $options = ['A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D']; //TODO add variability of options
                foreach ($questions as $row) {
                    $question = ModelQuiz::createFromActiveRow($row);
                    $select = $container->addRadioList(
                        'question' . $question->question_id,
                        $task->getFQName() . ' - ' . $question->getFQName(),
                        $options
                    );

                    $existingEntry = $this->submitQuizQuestionService->findByContestant(
                        $question,
                        $this->getContestant()
                    );
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

    /**
     * @throws StorageException
     */
    private function handleUploadFormSuccess(Form $form): void
    {
        $values = $form->getValues();

        Debugger::log(
            \sprintf('Contestant %d upload %s', $this->getContestant()->ct_id, $values['tasks']),
            'old-submit'
        );

        $taskIds = explode(',', $values['tasks']);
        $validIds = $this->getAvailableTasks()->fetchPairs('task_id', 'task_id');

        try {
            $this->submitService->explorer->getConnection()->beginTransaction();
            $this->uploadedSubmitStorage->beginTransaction();

            foreach ($taskIds as $taskId) {
                $questions = $this->quizQuestionService->getTable()->where('task_id', $taskId);
                /** @var ModelTask $task */
                $task = $this->taskService->findByPrimary($taskId);

                if (!isset($validIds[$taskId])) {
                    $this->flashMessage(
                        sprintf(_('Task %s cannot be submitted anymore.'), $task->label),
                        self::FLASH_ERROR
                    );
                    continue;
                }
                /** @var FileUpload[]|string[] $taskValues */
                $taskValues = $values['task' . $task->task_id];

                if (count($questions)) {
                    // Verification if user has event submitted any answer
                    $anySubmit = false;
                    foreach ($questions as $question) {
                        $name = 'question' . $question->question_id;
                        $answer = $taskValues[$name];
                        if ($answer != null) {
                            $anySubmit = true;
                            $this->submitQuizQuestionService->saveSubmittedQuestion(
                                $question,
                                $this->getContestant(),
                                $answer
                            );
                        }
                    }

                    // If there are no submitted quiz answers, continue
                    if (!$anySubmit) {
                        continue;
                    }

                    $this->submitHandlerFactory->handleQuizSubmit($task, $this->getContestant());
                } else {
                    if (!isset($taskValues['file'])) { // upload field was disabled
                        continue;
                    }
                    if (!$taskValues['file']->isOk()) {
                        Debugger::log(
                            sprintf('Uploaded file error %s.', $taskValues['file']->getError()),
                            Debugger::WARNING
                        );
                        continue;
                    }
                    $this->submitHandlerFactory->handleSave($taskValues['file'], $task, $this->getContestant());
                }
                $this->flashMessage(sprintf(_('Task %s submitted.'), $task->label), self::FLASH_SUCCESS);
            }

            $this->uploadedSubmitStorage->commit();
            $this->submitService->explorer->getConnection()->commit();
            $this->redirect('this');
        } catch (ModelException | ProcessingException $exception) {
            $this->uploadedSubmitStorage->rollback();
            $this->submitService->explorer->getConnection()->rollBack();
            Debugger::log($exception);
            $this->flashMessage(_('Task storing error.'), self::FLASH_ERROR);
        }
    }

    protected function createComponentSubmitsGrid(): SubmitsGrid
    {
        return new SubmitsGrid($this->getContext(), $this->getContestant());
    }

    protected function createComponentSubmitContainer(): SubmitContainer
    {
        return new SubmitContainer($this->getContext(), $this->getContestant());
    }
}
