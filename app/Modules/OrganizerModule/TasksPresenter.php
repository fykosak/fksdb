<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Components\Controls\Inbox\HandoutFormComponent;
use FKSDB\Components\Controls\Inbox\PointsVariance\ChartComponent;
use FKSDB\Components\Controls\Inbox\TaskImportFormComponent;
use FKSDB\Components\Grids\TaskGrid;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\ORM\Services\TaskService;
use FKSDB\Modules\Core\PresenterTraits\ContestYearEntityTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use FKSDB\Modules\Core\PresenterTraits\NoContestYearAvailable;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\UI\Control;
use Nette\Security\Resource;

final class TasksPresenter extends BasePresenter
{
    /** @use ContestYearEntityTrait<TaskModel> */
    use ContestYearEntityTrait;

    private TaskService $taskService;

    public function injectService(TaskService $taskService): void
    {
        $this->taskService = $taskService;
    }

    public function titleImport(): PageTitle
    {
        return new PageTitle(null, _('Task import'), 'fas fa-download');
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedImport(): bool
    {
        return $this->contestAuthorizator->isAllowed('task', 'insert', $this->getSelectedContest());
    }

    public function titleDispatch(): PageTitle
    {
        return new PageTitle(null, _('Handout'), 'fas fa-folder-open');
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedDispatch(): bool
    {
        return $this->contestAuthorizator->isAllowed('task', 'dispatch', $this->getSelectedContest());
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Tasks'), 'fas fa-folder-open');
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedList(): bool
    {
        return $this->contestAuthorizator->isAllowed('task', 'list', $this->getSelectedContest());
    }

    /**
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    protected function createComponentSeriesForm(): TaskImportFormComponent
    {
        return new TaskImportFormComponent($this->getContext(), $this->getSelectedContestYear());
    }

    /**
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    protected function createComponentHandoutForm(): HandoutFormComponent
    {
        return new HandoutFormComponent(
            $this->getContext(),
            $this->getSelectedContestYear(),
            $this->getSelectedSeries()
        );
    }

    /**
     * @param Resource|string|null $resource
     * @throws NoContestAvailable
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $this->getSelectedContest());
    }

    protected function getORMService(): TaskService
    {
        return $this->taskService;
    }

    protected function createComponentCreateForm(): Control
    {
        throw new NotImplementedException();
    }

    protected function createComponentEditForm(): Control
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    protected function createComponentGrid(): TaskGrid
    {
        return new TaskGrid($this->getContext(), $this->getSelectedContestYear(), $this->getSelectedSeries());
    }

    /**
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    protected function createComponentPointsVarianceChart(): ChartComponent
    {
        return new ChartComponent($this->getContext(), $this->getSelectedContestYear(), $this->getSelectedSeries());
    }
}
