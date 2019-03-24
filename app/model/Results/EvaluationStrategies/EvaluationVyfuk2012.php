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
class EvaluationVyfuk2012 extends EvaluationStrategy {

    /**
     * @return array|null
     */
    public function getCategories(): array {
        return [
            new ModelCategory(ModelCategory::CAT_ES_6),
            new ModelCategory(ModelCategory::CAT_ES_7),
            new ModelCategory(ModelCategory::CAT_ES_8),
            new ModelCategory(ModelCategory::CAT_ES_9),
            new ModelCategory(ModelCategory::CAT_UNK),
        ];
    }

    /**
     * @param ModelCategory $category
     * @return array|int|null
     */
    public function categoryToStudyYears(ModelCategory $category) {
        switch ($category->id) {
            case ModelCategory::CAT_ES_6:
                return [6];
            case ModelCategory::CAT_ES_7:
                return [7];
            case ModelCategory::CAT_ES_8:
                return [8];
            case ModelCategory::CAT_ES_9:
                return [9];
            case ModelCategory::CAT_UNK:
                return null;
            default:
                throw new Nette\InvalidArgumentException('Invalid category ' . $category->id);
                break;
        }
    }

    /**
     * @param Row $task
     * @return string
     */
    public function getPointsColumn(Row $task): string {
        return "s.raw_points";
    }

    /**
     * @return string
     */
    public function getSumColumn(): string {
        return "s.raw_points";
    }

    /**
     * @param Row $task
     * @param ModelCategory $category
     * @return int
     */
    public function getTaskPoints(Row $task, ModelCategory $category): int {
        return $task->points;
    }

    /**
     * @param ModelCategory $category
     * @return string
     */
    public function getTaskPointsColumn(ModelCategory $category): string {
        return 'IF(s.raw_points IS NOT NULL, t.points, NULL)';
    }
}
