<?php

declare(strict_types=1);

namespace FKSDB\Modules\PublicModule;

use FKSDB\Components\Controls\AjaxSubmit\Quiz\QuizComponent;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\ORM\Services\TaskService;
use FKSDB\Models\Submits\TaskNotFoundException;
use FKSDB\Modules\Core\BasePresenter;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;

class QuizRegisterPresenter extends BasePresenter
{
    /** @persistent */
    public ?int $id = null;

    private TaskService $taskService;

    final public function injectTernary(
        TaskService $taskService
    ): void {
        $this->taskService = $taskService;
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Submit a quiz'), 'fas fa-list');
    }

    protected function beforeRender(): void
    {
        /** @var TaskModel $task */
        $task = $this->taskService->findByPrimary($this->id);
        if ($task) {
            $this->getPageStyleContainer()->navBarClassName = 'bg-dark navbar-dark';
            $this->getPageStyleContainer()->navBrandPath = '/images/logo/white.svg';
            $this->getPageStyleContainer()->styleIds[] = $task->contest->getContestSymbol();
        }
        parent::beforeRender();
    }

    /**
     * @throws TaskNotFoundException
     * @throws ForbiddenRequestException
     */
    protected function createComponentQuizComponent(): QuizComponent
    {
        /** @var TaskModel $task */
        $task = $this->taskService->findByPrimary($this->id);
        if (!isset($task)) {
            throw new TaskNotFoundException();
        }

        // check if task is opened for submitting
        if (!$task->isOpened()) {
            throw new ForbiddenRequestException(sprintf(_('Task %s is not opened for submitting.'), $task->task_id));
        }

        return new QuizComponent($this->getContext(), $this->getLang(), $task, null);
    }
}
