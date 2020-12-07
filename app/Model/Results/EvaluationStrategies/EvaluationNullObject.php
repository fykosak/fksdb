<?php

namespace FKSDB\Model\Results\EvaluationStrategies;

use FKSDB\Model\Results\ModelCategory;
use Nette\Database\Table\ActiveRow;
use FKSDB\Model\Exceptions\NotImplementedException;

/**
 * Null Object pattern for FKSDB\Results\EvaluationStrategies\IEvaluationStrategy.
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class EvaluationNullObject extends EvaluationStrategy {

    /**
     * @return array|void
     * @throws NotImplementedException
     */
    public function getCategories(): array {
        throw new NotImplementedException();
    }

    /**
     * @param ModelCategory $category
     * @return array|void
     * @throws NotImplementedException
     */
    public function categoryToStudyYears(ModelCategory $category): array {
        throw new NotImplementedException();
    }

    /**
     * @param ActiveRow $task
     * @return string
     * @throws NotImplementedException
     */
    public function getPointsColumn(ActiveRow $task): string {
        throw new NotImplementedException();
    }

    /**
     * @return string
     * @throws NotImplementedException
     */
    public function getSumColumn(): string {
        throw new NotImplementedException();
    }

    /**
     * @param ActiveRow $task
     * @param ModelCategory $category
     * @return int|null
     * @throws NotImplementedException
     */
    public function getTaskPoints(ActiveRow $task, ModelCategory $category): ?int {
        throw new NotImplementedException();
    }

    /**
     * @param ModelCategory $category
     * @return string
     * @throws NotImplementedException
     */
    public function getTaskPointsColumn(ModelCategory $category): string {
        throw new NotImplementedException();
    }

}
