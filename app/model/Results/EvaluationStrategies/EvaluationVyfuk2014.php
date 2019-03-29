<?php

namespace FKSDB\Results\EvaluationStrategies;

use FKSDB\ORM\Models\ModelTask;
use FKSDB\Results\ModelCategory;
use Nette\Database\Table\ActiveRow;
use Nette\InvalidArgumentException;

/**
 * Introduced in Výfuk 2014 (4th official year).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class EvaluationVyfuk2014 extends EvaluationStrategy {

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
     * @return array
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
                throw new InvalidArgumentException('Invalid category ' . $category->id);
                break;
        }
    }

    /**
     * @param ActiveRow|ModelTask $task
     * @return string
     */
    public function getPointsColumn(ActiveRow $task): string {
        if ($task->label == '1') {
            return "IF (t.series < 7, (IF (ct.study_year NOT IN (6, 7), null, s.raw_points)), s.raw_points)";
        } else {
            return "s.raw_points";
        }
    }

    /**
     * @return string
     */
    public function getSumColumn(): string {
        return "IF (t.series < 7, IF (t.label IN ('1'), IF ( ct.study_year NOT IN (6, 7), null, s.raw_points), s.raw_points), s.raw_points)";
    }

    /**
     * @param ActiveRow|ModelTask $task
     * @param ModelCategory $category
     * @return int|null
     */
    public function getTaskPoints(ActiveRow $task, ModelCategory $category) {
        if ($task->label == '1' && $task->series < 7) {
            if (in_array($category->id, [
                ModelCategory::CAT_ES_6,
                ModelCategory::CAT_ES_7,
            ])) {
                return $task->points;
            } else {
                return null;
            }
        } else {
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
                return "IF (s.raw_points IS NOT NULL, t.points, NULL)";
                break;
            default:
                return "IF (s.raw_points IS NOT NULL, IF (t.series < 7, IF (t.label IN ('1'), NULL, t.points), NULL), NULL)";
        }
    }

}
