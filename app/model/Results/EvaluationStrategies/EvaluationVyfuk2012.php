<?php

namespace FKSDB\Results\EvaluationStrategies;

use FKSDB\ORM\Models\ModelTask;
use FKSDB\Results\ModelCategory;
use Nette;
use Nette\Database\Table\ActiveRow;

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

    public function getPointsColumn(ActiveRow $task): string {
        return "s.raw_points";
    }

    public function getSumColumn(): string {
        return "s.raw_points";
    }

    /**
     * @param ActiveRow|ModelTask $task
     * @param ModelCategory $category
     * @return int
     */
    public function getTaskPoints(ActiveRow $task, ModelCategory $category): int {
        return $task->points;
    }

    public function getTaskPointsColumn(ModelCategory $category): string {
        return 'IF(s.raw_points IS NOT NULL, t.points, NULL)';
    }
}
