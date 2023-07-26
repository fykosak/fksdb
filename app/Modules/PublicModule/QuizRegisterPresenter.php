<?php

declare(strict_types=1);

namespace FKSDB\Modules\PublicModule;

use FKSDB\Components\Controls\Upload\Quiz\QuizComponent;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\ORM\Services\TaskService;
use FKSDB\Models\Submits\TaskNotFoundException;
use FKSDB\Modules\Core\BasePresenter;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;

final class QuizRegisterPresenter extends BasePresenter
{
    /** @persistent */
    public ?int $id = null;

    private TaskService $taskService;

    final public function injectTernary(TaskService $taskService): void
    {
        $this->taskService = $taskService;
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Submit a quiz'), 'fas fa-list');
    }

    public function requiresLogin(): bool
    {
        return false;
    }

    public function authorizedDefault(): bool
    {
        return true;
    }

    protected function getStyleId(): string
    {
        /** @var TaskModel|null $task */
        $task = $this->taskService->findByPrimary($this->id);
        if (isset($task)) {
            return 'contest-' . $task->contest->getContestSymbol();
        }
        return parent::getStyleId();
    }

    /**
     * @throws TaskNotFoundException
     * @throws ForbiddenRequestException
     */
    protected function createComponentQuizComponent(): QuizComponent
    {
        /** @var TaskModel|null $task */
        $task = $this->taskService->findByPrimary($this->id);
        if (!isset($task)) {
            throw new TaskNotFoundException();
        }

        // check if task is opened for submitting
        if (!$task->isOpened()) {
            throw new ForbiddenRequestException(sprintf(_('Task %s is not opened for submitting.'), $task->task_id));
        }

        return new QuizComponent($this->getContext(), $task, null);
    }
}
