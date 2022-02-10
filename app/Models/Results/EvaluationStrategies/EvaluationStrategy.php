<?php

declare(strict_types=1);

namespace FKSDB\Models\Results\EvaluationStrategies;

use FKSDB\Models\ORM\Models\ModelTask;
use FKSDB\Models\Results\ModelCategory;

interface EvaluationStrategy
{

    /**
     * Should return SQL expression with points for given task.
     * There are available tables 'contestant' aliased to 'ct' and
     * 'submit' aliased to 's'.
     */
    public function getPointsColumn(ModelTask $task): string;

    /**
     * Should return SQL expression with points for given submit.
     * There are available tables 'contestant' aliased to 'ct',
     * 'submit' aliased to 's' and 'task' to 't'.
     * The returned expression is summed over group by series and contestant.
     */
    public function getSumColumn(): string;

    /**
     * @return array of int (study years of students with category)
     */
    public function categoryToStudyYears(ModelCategory $category): array;

    /**
     * @return ModelCategory[]
     */
    public function getCategories(): array;

    /**
     * Should return points for correctly solved task (aka Student Pilný) as part
     * of SQL query.
     * For columns available see getSumColumn.
     */
    public function getTaskPointsColumn(ModelCategory $category): string;

    /**
     * Should return points for correctly solved task (aka Student Pilný).
     */
    public function getTaskPoints(ModelTask $task, ModelCategory $category): ?int;
}
