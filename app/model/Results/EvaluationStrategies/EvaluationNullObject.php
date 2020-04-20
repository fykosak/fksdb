<?php

namespace FKSDB\Results\EvaluationStrategies;

use FKSDB\Results\ModelCategory;
use Nette\Database\Table\ActiveRow;
use FKSDB\Exceptions\NotImplementedException;

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
        throw new NotImplementedException;
    }

    /**
     * @param ModelCategory $category
     * @return array|void
     * @throws NotImplementedException
     */
    public function categoryToStudyYears(ModelCategory $category): array {
        throw new NotImplementedException;
    }

    /**
     * @param ActiveRow $task
     * @return string|void
     * @throws NotImplementedException
     */
    public function getPointsColumn(ActiveRow $task) {
        throw new NotImplementedException;
    }

    /**
     * @return string|void
     * @throws NotImplementedException
     */
    public function getSumColumn() {
        throw new NotImplementedException;
    }

    /**
     * @param ActiveRow $task
     * @param ModelCategory $category
     * @return int|void
     * @throws NotImplementedException
     */
    public function getTaskPoints(ActiveRow $task, ModelCategory $category) {
        throw new NotImplementedException;
    }

    /**
     * @param ModelCategory $category
     * @return string
     * @throws NotImplementedException
     */
    public function getTaskPointsColumn(ModelCategory $category): string {
        throw new NotImplementedException;
    }

}
