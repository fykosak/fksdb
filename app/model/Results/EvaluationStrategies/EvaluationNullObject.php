<?php

namespace FKSDB\Results\EvaluationStrategies;

use FKSDB\Results\ModelCategory;
use Nette\Database\Row;
use Nette\NotImplementedException;

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
     * @param Row $task
     * @return string|void
     * @throws NotImplementedException
     */
    public function getPointsColumn(Row $task) {
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
     * @param Row $task
     * @param ModelCategory $category
     * @return int|void
     * @throws NotImplementedException
     */
    public function getTaskPoints(Row $task, ModelCategory $category) {
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
