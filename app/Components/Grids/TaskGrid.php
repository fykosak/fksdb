<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Components\Grids\Components\Referenced\TemplateItem;
use FKSDB\Components\Grids\Components\Renderer\RendererItem;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\TaskContributionModel;
use FKSDB\Models\ORM\Models\TaskContributionType;
use FKSDB\Models\ORM\Models\TaskModel;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container as DIContainer;

/**
 * @phpstan-extends BaseGrid<TaskModel>
 */
class TaskGrid extends BaseGrid
{

    private ContestYearModel $contestYear;
    private int $series;

    public function __construct(DIContainer $container, ContestYearModel $contestYear, int $series)
    {
        parent::__construct($container);
        $this->contestYear = $contestYear;
        $this->series = $series;
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->addColumn(
        /** @phpstan-ignore-next-line */
            new TemplateItem($this->container, '@task.label', '@task.label:title'),
            'label'
        );
        $this->addColumn(
        /** @phpstan-ignore-next-line */
            new TemplateItem($this->container, '@task.name', '@task.name:title'),
            'name'
        );
        $this->addColumn(
        /** @phpstan-ignore-next-line */
            new TemplateItem($this->container, '@task.points', '@task.points:title'),
            'points'
        );
        $this->addColumn(
        /** @phpstan-ignore-next-line */
            new TemplateItem($this->container, '@task.submit_start', '@task.submit_start:title'),
            'submit_start'
        );
        $this->addColumn(
        /** @phpstan-ignore-next-line */
            new TemplateItem($this->container, '@task.submit_deadline', '@task.submit_deadline:title'),
            'submit_deadline'
        );
        $this->addContributionField(TaskContributionType::from(TaskContributionType::SOLUTION));
        $this->addContributionField(TaskContributionType::from(TaskContributionType::AUTHOR));
        $this->addContributionField(TaskContributionType::from(TaskContributionType::GRADE));

        $this->addColumn(
        /** @phpstan-ignore-next-line */
            new TemplateItem($this->container, '@task.average_points', '@task.average_points:title'),
            'average_points'
        );
        $this->addColumn(
        /** @phpstan-ignore-next-line */
            new TemplateItem($this->container, '@task.solvers_count', '@task.solvers_count:title'),
            'solvers_count'
        );
    }

    private function addContributionField(TaskContributionType $contributionType): void
    {
        $this->addColumn(
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
