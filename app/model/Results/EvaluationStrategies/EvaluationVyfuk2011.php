<?php

namespace FKSDB\Results\EvaluationStrategies;

use FKSDB\Results\ModelCategory;
use Nette;
use Nette\Database\Row;

/**
 * Introduced in Výfuk 2011 (1st official year).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class EvaluationVyfuk2011 extends EvaluationStrategy {
    /**
     * @return array|null
     */
    public function getCategories(): array {
        return [
            new ModelCategory(ModelCategory::CAT_ES_6),
            new ModelCategory(ModelCategory::CAT_ES_7),
            new ModelCategory(ModelCategory::CAT_ES_8),
            new ModelCategory(ModelCategory::CAT_ES_9),
        ];
    }

    /**
     * @param ModelCategory $category
     * @return array|int
     */
    public function categoryToStudyYears(ModelCategory $category): array {
        switch ($category->id) {
            case ModelCategory::CAT_ES_6:
                return [6];
            case ModelCategory::CAT_ES_7:
                return [7];
            case ModelCategory::CAT_ES_8:
                return [8];
            case ModelCategory::CAT_ES_9:
                return [null, 9];
            default:
                throw new Nette\InvalidArgumentException('Invalid category ' . $category->id);
                break;
        }
    }

    /**
     * @param Row $task
     * @return string
     */
    public function getPointsColumn(Row $task) {
        return "s.raw_points";
    }

    /**
     * @return string
     */
    public function getSumColumn() {
        return "s.raw_points";
    }

    /**
     * @param Row $task
     * @param ModelCategory $category
     * @return int
     */
    public function getTaskPoints(Row $task, ModelCategory $category) {
        return $task->points;
    }

    /**
     * @param ModelCategory $category
     * @return int|string
     */
    public function getTaskPointsColumn(ModelCategory $category): string {
        return 'IF(s.raw_points IS NOT NULL, t.points, NULL)';
    }
}
