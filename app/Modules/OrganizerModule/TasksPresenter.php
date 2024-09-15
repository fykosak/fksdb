<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Components\Grids\TaskGrid;
use FKSDB\Components\Inbox\HandoutFormComponent;
use FKSDB\Components\Inbox\PointsVariance\ChartComponent;
use FKSDB\Components\Inbox\TaskImportFormComponent;
use FKSDB\Models\Authorization\Resource\PseudoContestResource;
use FKSDB\Models\Authorization\Resource\PseudoContestYearResource;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\ORM\Services\TaskService;
use FKSDB\Modules\Core\PresenterTraits\ContestYearEntityTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use FKSDB\Modules\Core\PresenterTraits\NoContestYearAvailable;
use Fykosak\Utils\UI\PageTitle;

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
        return $this->contestAuthorizator->isAllowed(
            new PseudoContestResource(TaskModel::RESOURCE_ID, $this->getSelectedContest()),
            'insert',
            $this->getSelectedContest()
        );
    }

    public function titleDispatch(): PageTitle
    {
        return new PageTitle(null, _('Handout'), 'fas fa-folder-open');
    }

    /**
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    public function authorizedDispatch(): bool
    {
        return $this->contestYearAuthorizator->isAllowed(
            new PseudoContestYearResource(TaskModel::RESOURCE_ID, $this->getSelectedContestYear()),
            'dispatch',
            $this->getSelectedContestYear()
        );
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Tasks'), 'fas fa-folder-open');
    }

    /**
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    public function authorizedList(): bool
    {
        return $this->contestYearAuthorizator->isAllowed(
            new PseudoContestYearResource(TaskModel::RESOURCE_ID, $this->getSelectedContestYear()),
            'list',
            $this->getSelectedContestYear()
        );
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

    protected function getORMService(): TaskService
    {
        return $this->taskService;
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
