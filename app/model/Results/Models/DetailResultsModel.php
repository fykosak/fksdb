<?php

namespace FKSDB\Results\Models;

use FKSDB\ORM\Models\ModelTask;
use FKSDB\Results\ModelCategory;
use Nette\InvalidStateException;

/**
 * Detailed results of a single series. Number of tasks is dynamic.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class DetailResultsModel extends AbstractResultsModel {

    /**
     * @var int
     */
    protected $series;

    /**
     * Cache
     * @var array
     */
    private $dataColumns = [];

    /**
     * Definition of header.
     *
     * @param ModelCategory $category
     * @return array
     */
    public function getDataColumns(ModelCategory $category): array {
        if (!isset($this->dataColumns[$category->id])) {
            $dataColumns = [];
            $sum = 0;
            /** @var ModelTask $task */
            foreach ($this->getTasks($this->series) as $task) {
                $taskPoints = $this->evaluationStrategy->getTaskPoints($task, $category);
                $dataColumns[] = [
                    self::COL_DEF_LABEL => $task->label,
                    self::COL_DEF_LIMIT => $taskPoints,
                    self::COL_ALIAS => self::DATA_PREFIX . count($dataColumns),
                ];
                $sum += $taskPoints;
            }
            $dataColumns[] = [
                self::COL_DEF_LABEL => self::LABEL_SUM,
                self::COL_DEF_LIMIT => $sum,
                self::COL_ALIAS => self::ALIAS_SUM,
            ];
            $this->dataColumns[$category->id] = $dataColumns;
        }
        return $this->dataColumns[$category->id];
    }

    /**
     * @return int|mixed
     */
    public function getSeries() {
        return $this->series;
    }

    /**
     * @param mixed $series
     */
    public function setSeries($series) {
        $this->series = $series;
        // invalidate cache of columns
        $this->dataColumns = [];
    }

    /**
     * @return ModelCategory[]
     */
    public function getCategories(): array {
        return $this->evaluationStrategy->getCategories();
    }

    /**
     * @param ModelCategory $category
     * @return string
     */
    protected function composeQuery(ModelCategory $category): string {
        if (!$this->series) {
            throw new InvalidStateException('Series not set.');
        }

        $select = [];
        $select[] = "IF(p.display_name IS NULL, CONCAT(p.other_name, ' ', p.family_name), p.display_name) AS `" . self::DATA_NAME . "`";
        $select[] = "sch.name_abbrev AS `" . self::DATA_SCHOOL . "`";

        $tasks = $this->getTasks($this->series);
        $i = 0;
        /** @var ModelTask $task */
        foreach ($tasks as $task) {
            $points = $this->evaluationStrategy->getPointsColumn($task);
            $select[] = "round(MAX(IF(t.task_id = " . $task->task_id . ", " . $points . ", null))) AS '" . self::DATA_PREFIX . $i . "'";
            $i += 1;
        }
        $sum = $this->evaluationStrategy->getSumColumn();
        $select[] = "round(SUM($sum)) AS '" . self::ALIAS_SUM . "'";

        $from = " from v_contestant ct
left join person p using(person_id)
left join school sch using(school_id)
left join task t ON t.year = ct.year AND t.contest_id = ct.contest_id
left join submit s ON s.task_id = t.task_id AND s.ct_id = ct.ct_id";

        $conditions = [
            'ct.year' => $this->year,
            'ct.contest_id' => $this->contest->contest_id,
            't.series' => $this->series,
            'ct.study_year' => $this->evaluationStrategy->categoryToStudyYears($category),
        ];

        $query = "select " . implode(', ', $select);
        $query .= $from;

        $where = $this->conditionsToWhere($conditions);
        $query .= " where $where";

        $query .= " group by p.person_id, sch.name_abbrev "; //abuse MySQL misimplementation of GROUP BY
        $query .= " order by `" . self::ALIAS_SUM . "` DESC, p.family_name ASC, p.other_name ASC";

        $dataAlias = 'data';
        return "select $dataAlias.*, @rownum := @rownum + 1, @rank := IF($dataAlias." . self::ALIAS_SUM . " = @prevSum or ($dataAlias." . self::ALIAS_SUM . " is null and @prevSum is null), @rank, @rownum) AS `" . self::DATA_RANK_FROM . "`, @prevSum := $dataAlias." . self::ALIAS_SUM . "
        from ($query) data, (select @rownum := 0, @rank := 0, @prevSum := -1) init";
    }

}
