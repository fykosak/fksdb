<?php

namespace FKSDB\Results\EvaluationStrategies;

use FKSDB\ORM\Models\ModelTask;
use FKSDB\Results\ModelCategory;
use Nette;
use Nette\Database\Table\ActiveRow;

/**
 * First two categories have doubled points for the first two problems.
 * Introduced in FYKOS 2011 (25 th year).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class EvaluationFykos2011 extends EvaluationStrategy {

    /**
     * @return array|null
     */
    public function getCategories(): array {
        return [
            new ModelCategory(ModelCategory::CAT_HS_1),
            new ModelCategory(ModelCategory::CAT_HS_2),
            new ModelCategory(ModelCategory::CAT_HS_3),
            new ModelCategory(ModelCategory::CAT_HS_4),
        ];
    }

    /**
     * @param ModelCategory $category
     * @return array|int
     */
    public function categoryToStudyYears(ModelCategory $category): array {
        switch ($category->id) {
            case ModelCategory::CAT_HS_1:
                return [6, 7, 8, 9, 1];
            case ModelCategory::CAT_HS_2:
                return [2];
            case ModelCategory::CAT_HS_3:
                return [3];
            case ModelCategory::CAT_HS_4:
                return [null, 4];
            default:
                throw new Nette\InvalidArgumentException('Invalid category ' . $category->id);
                break;
        }
    }

    /**
     * @param ActiveRow|ModelTask $task
     * @return string
     */
    public function getPointsColumn(ActiveRow $task): string {
        if ($task->label == '1' || $task->label == '2') {
            return "IF(ct.study_year IN (6,7,8,9,1,2), 2 * s.raw_points, s.raw_points)";
        } else {
            return "s.raw_points";
        }
    }

    /**
     * @return string
     */
    public function getSumColumn(): string {
        return "IF(t.label IN ('1', '2'), IF(ct.study_year IN (6,7,8,9,1,2), 2 * s.raw_points, s.raw_points), s.raw_points)";
    }

    /**
     * @param ActiveRow|ModelTask $task
     * @param ModelCategory $category
     * @return float|int
     */
    public function getTaskPoints(ActiveRow $task, ModelCategory $category) {
        switch ($category->id) {
            case ModelCategory::CAT_ES_6:
            case ModelCategory::CAT_ES_7:
            case ModelCategory::CAT_ES_8:
            case ModelCategory::CAT_ES_9:
            case ModelCategory::CAT_HS_1:
            case ModelCategory::CAT_HS_2:
                if ($task->label == '1' || $task->label == '2') {
                    return $task->points * 2;
                } else {
                    return $task->points;
                }
                break;
            default:
                return $task->points;
        }
    }

    /**
     * @param ModelCategory $category
     * @return string
     */
    public function getTaskPointsColumn(ModelCategory $category): string {
        switch ($category->id) {
            case ModelCategory::CAT_ES_6:
            case ModelCategory::CAT_ES_7:
            case ModelCategory::CAT_ES_8:
            case ModelCategory::CAT_ES_9:
            case ModelCategory::CAT_HS_1:
            case ModelCategory::CAT_HS_2:
                return "IF(s.raw_points IS NOT NULL, IF(t.label IN ('1', '2'), 2 * t.points, t.points), NULL)";
                break;
            default:
                return 'IF(s.raw_points IS NOT NULL, t.points, NULL)';
        }

    }

}
