<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Upload\Legacy;

use FKSDB\Components\Controls\FormComponent\FormComponent;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\SubmitSource;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\ORM\Services\SubmitService;
use FKSDB\Models\ORM\Services\TaskService;
use FKSDB\Models\Submits\ProcessingException;
use FKSDB\Models\Submits\SubmitHandlerFactory;
use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\Utils\Localization\GettextTranslator;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Nette\Http\FileUpload;
use Tracy\Debugger;

/**
 * @property GettextTranslator $translator
 */
class LegacyUploadFormComponent extends FormComponent
{
    private ContestantModel $contestant;
    private SubmitService $submitService;
    private TaskService $taskService;
    private SubmitHandlerFactory $submitHandlerFactory;

    public function __construct(Container $container, ContestantModel $contestant)
    {
        parent::__construct($container);
        $this->contestant = $contestant;
    }

    final public function injectTernary(
        SubmitService $submitService,
        TaskService $taskService,
        SubmitHandlerFactory $submitHandlerFactory
    ): void {
        $this->submitService = $submitService;
        $this->taskService = $taskService;
        $this->submitHandlerFactory = $submitHandlerFactory;
    }

    protected function handleSuccess(Form $form): void
    {
        $values = $form->getValues();
        Debugger::log(
            \sprintf('Contestant %d upload %s', $this->contestant->contestant_id, $values['tasks']),
            'old-submit'
        );

        $taskIds = explode(',', $values['tasks']);
        $validIds = $this->getAvailableTasks()->fetchPairs('task_id', 'task_id');

        try {
            $this->submitService->explorer->getConnection()->beginTransaction();
            $this->submitHandlerFactory->uploadedStorage->beginTransaction();

            foreach ($taskIds as $taskId) {
                /** @var TaskModel|null $task */
                $task = $this->taskService->findByPrimary($taskId);

                if (!isset($validIds[$taskId])) {
                    $this->flashMessage(
                        sprintf(_('Task %s cannot be submitted anymore.'), $task->label),
                        Message::LVL_ERROR
                    );
                    continue;
                }
                /** @var array{'file':FileUpload|null} $taskValues */
                $taskValues = $values['task' . $task->task_id];
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
                $this->submitHandlerFactory->handleSave($taskValues['file'], $task, $this->contestant);

                $this->flashMessage(sprintf(_('Task %s submitted.'), $task->label), Message::LVL_SUCCESS);
            }

            $this->submitHandlerFactory->uploadedStorage->commit();
            $this->submitService->explorer->getConnection()->commit();
            $this->redirect('this');
        } catch (ModelException | ProcessingException $exception) {
            $this->submitHandlerFactory->uploadedStorage->rollback();
            $this->submitService->explorer->getConnection()->rollBack();
            Debugger::log($exception);
            $this->flashMessage(_('Task storing error.'), Message::LVL_ERROR);
        }
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('upload', _('Submit'));
    }

    protected function configureForm(Form $form): void
    {
        $taskIds = [];
        if (!$this->contestant->contest_category) {
            $this->flashMessage(_('Contestant is missing study year. Not all tasks are thus available.'));
        }
        $prevDeadline = null;
        /** @var TaskModel $task */
        foreach ($this->getAvailableTasks() as $task) {
            if ($task->submit_deadline !== $prevDeadline) {
                $form->addGroup(sprintf(_('Deadline %s'), $task->submit_deadline->format(_('__date_time'))));
            }
            $submit = $this->submitService->findByContestant($this->contestant, $task);
            if ($submit && $submit->source->value == SubmitSource::POST) {
                continue; // prevDeadline will work though
            }
            $container = new ContainerWithOptions($this->getContext());
            $form->addComponent($container, 'task' . $task->task_id);
            //$container = $form->addContainer();

            $upload = $container->addUpload('file', $task->getFullLabel($this->translator->lang));
            $conditionedUpload = $upload
                ->addCondition(Form::FILLED)
                ->addRule(
                    Form::MIME_TYPE,
                    _('Only PDF files are accepted.'),
                    'application/pdf'
                );

            if (!$task->isForCategory($this->contestant->contest_category)) {
                $upload->setOption('description', _('Task is not for your category.'));
                $upload->setDisabled();
            }

            if ($submit && $this->submitHandlerFactory->uploadedStorage->fileExists($submit)) {
                $overwrite = $container->addCheckbox('overwrite', _('Overwrite submitted solutions.'));
                $conditionedUpload->addConditionOn($overwrite, Form::EQUAL, false)->addRule(
                    Form::BLANK,
                    _('Either tick overwrite the solution, or don\'t submit it.')
                );
            }

            $prevDeadline = $task->submit_deadline;
            $taskIds[] = $task->task_id;
        }

        if ($taskIds) {
            $form->addHidden('tasks', implode(',', $taskIds));
            $form->setCurrentGroup();
        }
    }

    /**
     * @phpstan-return TypedGroupedSelection<TaskModel>
     */
    private function getAvailableTasks(): TypedGroupedSelection
    {
        return $this->contestant->getContestYear()
            ->getAvailableTasks()
            ->order('ISNULL(submit_deadline) ASC, submit_deadline ASC');
    }
}
