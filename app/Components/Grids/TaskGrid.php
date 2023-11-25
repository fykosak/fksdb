<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Components\Grids\Components\Referenced\SimpleItem;
use FKSDB\Components\Grids\Components\Renderer\RendererItem;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\TaskContributionModel;
use FKSDB\Models\ORM\Models\TaskContributionType;
use FKSDB\Models\ORM\Models\TaskModel;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @phpstan-extends BaseGrid<TaskModel,array{}>
 */
final class TaskGrid extends BaseGrid
{
    private ContestYearModel $contestYear;
    private int $series;

    public function __construct(Container $container, ContestYearModel $contestYear, int $series)
    {
        parent::__construct($container);
        $this->contestYear = $contestYear;
        $this->series = $series;
    }

    protected function configure(): void
    {
        $this->paginate = false;
        $this->counter = false;
        $this->filtered = false;
        /** @phpstan-ignore-next-line */
        $this->addTableColumn(
        /** @phpstan-ignore-next-line */
            new SimpleItem($this->container, '@task.label'),
            'label'
        );
        /** @phpstan-ignore-next-line */
        $this->addTableColumn(
        /** @phpstan-ignore-next-line */
            new SimpleItem($this->container, '@task.name'),
            'name'
        );
        /** @phpstan-ignore-next-line */
        $this->addTableColumn(
        /** @phpstan-ignore-next-line */
            new SimpleItem($this->container, '@task.points'),
            'points'
        );
        /** @phpstan-ignore-next-line */
        $this->addTableColumn(
        /** @phpstan-ignore-next-line */
            new SimpleItem($this->container, '@task.submit_start'),
            'submit_start'
        );
        /** @phpstan-ignore-next-line */
        $this->addTableColumn(
        /** @phpstan-ignore-next-line */
            new SimpleItem($this->container, '@task.submit_deadline'),
            'submit_deadline'
        );
        $this->addContributionField(TaskContributionType::from(TaskContributionType::SOLUTION));
        $this->addContributionField(TaskContributionType::from(TaskContributionType::AUTHOR));
        $this->addContributionField(TaskContributionType::from(TaskContributionType::GRADE));
        /** @phpstan-ignore-next-line */
        $this->addTableColumn(
        /** @phpstan-ignore-next-line */
            new SimpleItem($this->container, '@task.average_points'),
            'average_points'
        );
        /** @phpstan-ignore-next-line */
        $this->addTableColumn(
        /** @phpstan-ignore-next-line */
            new SimpleItem($this->container, '@task.solvers_count'),
            'solvers_count'
        );
    }

    private function addContributionField(TaskContributionType $contributionType): void
    {
        $this->addTableColumn(
            new RendererItem(
                $this->container,
                function (TaskModel $model) use ($contributionType) {
                    $contributions = $model->getContributions($contributionType);
                    $contributors = [];
                    /** @var TaskContributionModel $contribution */
                    foreach ($contributions as $contribution) {
                        $contributors[] = $contribution->person->getFullName();
                    }
                    return join(', ', $contributors);
                },
                new Title(null, $contributionType->label())
            ),
            'contribution_' . $contributionType->value
        );
    }

    /**
     * @return TypedGroupedSelection<TaskModel>
     */
    protected function getModels(): TypedGroupedSelection
    {
        return $this->contestYear->getTasks($this->series);
    }
}
