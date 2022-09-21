<?php

declare(strict_types=1);

namespace FKSDB\Models\Results\EvaluationStrategies;

use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\Results\ModelCategory;
use FKSDB\Models\Exceptions\NotImplementedException;

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
    public function getTaskPoints(TaskModel $task, ModelCategory $category): ?int
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    public function getTaskPointsColumn(ModelCategory $category): string
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    public function getSubmitPoints(SubmitModel $submit, ModelCategory $category): ?float
    {
        throw new NotImplementedException();
    }

    protected function getCategoryMap(): array
    {
        throw new NotImplementedException();
    }
}
