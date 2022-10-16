<?php

declare(strict_types=1);

namespace FKSDB\Models\Results\EvaluationStrategies;

use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\ContestCategoryModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\ORM\Services\ContestCategoryService;
use Nette\DI\Container;
use Nette\InvalidArgumentException;

abstract class EvaluationStrategy
{
    private Container $container;
    protected ContestCategoryService $contestCategoryService;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->container->callInjects($this);
    }

    public function injectCategoryService(ContestCategoryService $contestCategoryService): void
    {
        $this->contestCategoryService = $contestCategoryService;
    }

    /**
     * Should return SQL expression with points for given task.
     * There are available tables 'contestant' aliased to 'ct' and
     * 'submit' aliased to 's'.
     */
    abstract public function getPointsColumn(TaskModel $task): string;

    /**
     * Should return SQL expression with points for given submit.
     * There are available tables 'contestant' aliased to 'ct',
     * 'submit' aliased to 's' and 'task' to 't'.
     * The returned expression is summed over group by series and contestant.
     */
    abstract public function getSumColumn(): string;

    /**
     * @return array of int (study years of students with category)
     */
    final public function categoryToStudyYears(ContestCategoryModel $category): array
    {
        $map = $this->getCategoryMap();
        if (isset($map[$category->label])) {
            return $map[$category->label];
        }
        throw new InvalidArgumentException('Invalid category ' . $category->label);
    }

    final public function studyYearsToCategory(ContestantModel $contestant): ContestCategoryModel
    {
        $map = $this->getCategoryMap();
        $personHistory = $contestant->getPersonHistory();
        foreach ($map as $key => $values) {
            if (in_array($personHistory->study_year, $values, true)) {
                return $this->contestCategoryService->findByLabel((string)$key);
            }
        }
        throw new InvalidArgumentException(
            sprintf(
                _('Invalid studyYear %i for contestant %s.'),
                $personHistory->study_year,
                $contestant->person->getFullName()
            )
        );
    }

    abstract public function getSubmitPoints(SubmitModel $submit, ContestCategoryModel $category): ?float;

    abstract protected function getCategoryMap(): array;

    /**
     * @return ContestCategoryModel[]
     */
    final public function getCategories(): array
    {
        return array_map(
            fn($value) => $this->contestCategoryService->findByLabel((string)$value),
            array_keys($this->getCategoryMap())
        );
    }

    /**
     * Should return points for correctly solved task (aka Student Pilný) as part
     * of SQL query.
     * For columns available see getSumColumn.
     */
    abstract public function getTaskPointsColumn(ContestCategoryModel $category): string;

    /**
     * Should return points for correctly solved task (aka Student Pilný).
     */
    abstract public function getTaskPoints(TaskModel $task, ContestCategoryModel $category): ?float;
}
