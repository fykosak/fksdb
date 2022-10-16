<?php

declare(strict_types=1);

namespace FKSDB\Models\Results\EvaluationStrategies;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\ContestCategoryModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\TaskModel;

/**
 * Null Object pattern for FKSDB\Results\EvaluationStrategies\EvaluationStrategy.
 */
class EvaluationNullObject extends EvaluationStrategy
{
    /**
     * @throws NotImplementedException
     */
    public function getPointsColumn(TaskModel $task): string
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    public function getSumColumn(): string
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    public function getTaskPoints(TaskModel $task, ContestCategoryModel $category): ?float
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    public function getTaskPointsColumn(ContestCategoryModel $category): string
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    public function getSubmitPoints(SubmitModel $submit, ContestCategoryModel $category): ?float
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    protected function getCategoryMap(): array
    {
        throw new NotImplementedException();
    }
}
