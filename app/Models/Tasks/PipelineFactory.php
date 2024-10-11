<?php

declare(strict_types=1);

namespace FKSDB\Models\Tasks;

use FKSDB\Models\Pipeline\Pipeline;
use Nette\DI\Container;

/**
 * This is not real factory, it's only used as an internode for defining
 * pipelines inside Neon and inject them into presenters at once.
 */
class PipelineFactory
{
    /**
     * @see StudyYearsFromXML
     * @phpstan-var array<int,int[]>
     */
    private array $defaultCategories;

    private Container $container;

    /**
     * @phpstan-param array<int,int[]> $defaultCategories
     */
    public function __construct(
        array $defaultCategories,
        Container $container
    ) {
        $this->container = $container;
        $this->defaultCategories = $defaultCategories;
    }

    /**
     * @phpstan-return Pipeline<SeriesData>
     */
    public function create(): Pipeline
    {
        $pipeline = new Pipeline();

        // common stages
        $pipeline->stages[] = new TasksFromXML($this->container);
        $pipeline->stages[] = new DeadlineFromXML($this->container);
        $pipeline->stages[] = new ContributionsFromXML($this->container);
        $pipeline->stages[] =
            new StudyYearsFromXML(
                $this->defaultCategories,
                $this->container
            );

        return $pipeline;
    }
}
