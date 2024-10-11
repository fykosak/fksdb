<?php

declare(strict_types=1);

namespace FKSDB\Models\Results\EvaluationStrategies;

use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\ContestCategoryModel;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\ORM\Services\ContestantService;
use FKSDB\Models\ORM\Services\ContestCategoryService;
use Nette\DI\Container;
use Nette\InvalidArgumentException;

abstract class EvaluationStrategy
{
    private Container $container;
    protected ContestCategoryService $contestCategoryService;
    private ContestantService $contestantService;
    protected ContestYearModel $contestYear;

    public function __construct(Container $container, ContestYearModel $contestYear)
    {
        $this->container = $container;
        $this->contestYear = $contestYear;
        $this->container->callInjects($this);
    }

    public function injectService(
        ContestCategoryService $contestCategoryService,
        ContestantService $contestantService
    ): void {
        $this->contestCategoryService = $contestCategoryService;
        $this->contestantService = $contestantService;
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
     * @phpstan-return array<int,string|null>
     */
    final public function categoryToStudyYears(ContestCategoryModel $category): array
    {
        $map = $this->getCategoryMap();
        if (isset($map[$category->label])) {
            return $map[$category->label];
        }
        throw new InvalidArgumentException('Invalid category ' . $category->label);
    }

    final public function studyYearsToCategory(PersonModel $person): ContestCategoryModel
    {
        $map = $this->getCategoryMap();
        $personHistory = $person->getHistory($this->contestYear);
        foreach ($map as $key => $values) {
            if (in_array($personHistory->study_year_new->value, $values, true)) {
                return $this->contestCategoryService->findByLabel((string)$key);
            }
        }
        throw new InvalidArgumentException(
            sprintf(
                _('Invalid studyYear %d for person %s.'),
                $personHistory->study_year_new->label(),
                $person->getFullName()
            )
        );
    }

    final public function createContestant(PersonModel $person): ContestantModel
    {
        $category = $this->studyYearsToCategory($person);
        /** @var ContestantModel $contestant */
        $contestant = $this->contestantService->storeModel([
            'contest_id' => $this->contestYear->contest,
            'person_id' => $person->person_id,
            'year' => $this->contestYear->year,
            'contest_category_id' => $category->contest_category_id,
        ]);
        return $contestant;
    }

    final public function updateCategory(ContestantModel $contestant): ContestantModel
    {
        $category = $this->studyYearsToCategory($contestant->person);
        if ($category->contest_category_id === $contestant->contest_category_id) {
            return $contestant;
        }
        /** @var ContestantModel $contestant */
        $contestant = $this->contestantService->storeModel([
            'contest_category_id' => $category->contest_category_id,
        ], $contestant);
        return $contestant;
    }

    abstract public function getSubmitPoints(SubmitModel $submit): ?float;

    /**
     * @phpstan-return array<string,array<int,string>>
     */
    abstract protected function getCategoryMap(): array;

    /**
     * @phpstan-return ContestCategoryModel[]
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
