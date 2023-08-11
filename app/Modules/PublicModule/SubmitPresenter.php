<?php

declare(strict_types=1);

namespace FKSDB\Modules\PublicModule;

use FKSDB\Components\Controls\Upload\AjaxSubmit\SubmitContainer;
use FKSDB\Components\Controls\Upload\Legacy\LegacyUploadFormComponent;
use FKSDB\Components\Controls\Upload\Quiz\QuizComponent;
use FKSDB\Components\Grids\Submits\QuizAnswersGrid;
use FKSDB\Components\Grids\SubmitsGrid;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\ORM\Services\SubmitService;
use FKSDB\Models\ORM\Services\TaskService;
use FKSDB\Models\Submits\SubmitNotQuizException;
use FKSDB\Models\Submits\TaskNotFoundException;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use FKSDB\Modules\Core\PresenterTraits\NoContestYearAvailable;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;

final class SubmitPresenter extends BasePresenter
{
    /** @persistent */
    public ?int $id = null;
    private SubmitService $submitService;
    private TaskService $taskService;

    final public function inject(SubmitService $submitService, TaskService $taskService): void
    {
        $this->submitService = $submitService;
        $this->taskService = $taskService;
    }

    public function authorizedLegacy(): bool
    {
        return $this->authorizedDefault();
    }

    public function titleLegacy(): PageTitle
    {
        return new PageTitle(null, _('Legacy upload system'), 'fas fa-cloud-upload-alt');
    }

    public function renderLegacy(): void
    {
        $this->renderDefault();
    }

    public function authorizedDefault(): bool
    {
        return $this->contestAuthorizator->isAllowed('submit', 'upload', $this->getSelectedContest());
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Submit a solution'), 'fas fa-cloud-upload-alt');
    }

    public function renderDefault(): void
    {
        $this->template->hasTasks = $this->getSelectedContestYear()->isActive();
    }

    public function authorizedQuiz(): bool
    {
        return $this->authorizedDefault();
    }

    public function titleQuiz(): PageTitle
    {
        return new PageTitle(null, _('Submit a quiz'), 'fas fa-list');
    }

    public function titleQuizDetail(): PageTitle
    {
        return new PageTitle(null, _('Quiz detail'), 'fas fa-tasks');
    }

    public function authorizedQuizDetail(): bool
    {
        $submit = $this->submitService->findByPrimary($this->id);
        return $this->contestAuthorizator->isAllowed($submit, 'download', $this->getSelectedContest());
    }

    public function authorizedList(): bool
    {
        return $this->contestAuthorizator->isAllowed('submit', 'list', $this->getSelectedContest());
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Submitted solutions'), 'fas fa-cloud-upload-alt');
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

        return new QuizComponent($this->getContext(), $task, $this->getContestant());
    }

    /**
     * @throws SubmitNotQuizException
     */
    protected function createComponentQuizDetail(): QuizAnswersGrid
    {
        $submit = $this->submitService->findByPrimary($this->id); //TODO!!!!
        $deadline = $submit->task->submit_deadline;
        return new QuizAnswersGrid(
            $this->getContext(),
            $submit,
            $deadline ? $submit->task->submit_deadline->getTimestamp() < time() : false
        );
    }

    /**
     * @throws NotFoundException
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    protected function createComponentUploadForm(): LegacyUploadFormComponent
    {
        return new LegacyUploadFormComponent($this->getContext(), $this->getContestant());
    }

    /**
     * @throws NotFoundException
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    protected function createComponentSubmitsGrid(): SubmitsGrid
    {
        return new SubmitsGrid($this->getContext(), $this->getContestant());
    }

    /**
     * @throws NotFoundException
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    protected function createComponentSubmitContainer(): SubmitContainer
    {
        return new SubmitContainer($this->getContext(), $this->getContestant());
    }
}
