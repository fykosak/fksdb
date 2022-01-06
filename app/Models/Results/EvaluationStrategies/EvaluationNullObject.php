<?php

declare(strict_types=1);

namespace FKSDB\Models\Results\EvaluationStrategies;

use FKSDB\Models\ORM\Models\ModelTask;
use FKSDB\Models\Results\ModelCategory;
use Nette\Database\Table\ActiveRow;
use FKSDB\Models\Exceptions\NotImplementedException;

/**
 * Null Object pattern for FKSDB\Results\EvaluationStrategies\EvaluationStrategy.
 */
class EvaluationNullObject extends EvaluationStrategy {

    /**
     * @throws NotImplementedException
     */
    public function getCategories(): array {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    public function categoryToStudyYears(ModelCategory $category): array {
        throw new NotImplementedException();
    }

    /**
     * @param ActiveRow $task
     * @throws NotImplementedException
     */
    public function getPointsColumn(ActiveRow $task): string {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    public function getSumColumn(): string {
        throw new NotImplementedException();
    }

    /**
     * @param ActiveRow|ModelTask $task
     * @throws NotImplementedException
     */
    public function getTaskPoints(ActiveRow $task, ModelCategory $category): ?int {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    public function getTaskPointsColumn(ModelCategory $category): string {
        throw new NotImplementedException();
    }

}
