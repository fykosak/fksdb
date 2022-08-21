<?php

declare(strict_types=1);

namespace FKSDB\Models\Results\Models;

use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\Results\ModelCategory;
use Nette\InvalidStateException;

/**
 * Cumulative results (sums and percentage) for chosen series.
 */
class CumulativeResultsModel extends AbstractResultsModel
{
    /** @var int[] */
    protected array $series;

    /**
     * Cache
     * @var array
     */
    private array $dataColumns = [];

    /**
     * Definition of header.
     */
    public function getDataColumns(ModelCategory $category): array
    {
        if ($this->series === null) {
            throw new InvalidStateException('Series not specified.');
        }

        if (!isset($this->dataColumns[$category->value])) {
            $dataColumns = [];
            $sumLimit = $this->getSumLimit($category);
            $studentPilnySumLimit = $this->getSumLimitForStudentPilny();

            foreach ($this->getSeries() as $series) {
                $points = null;
                /** @var TaskModel $task */
                foreach ($this->getTasks($series) as $task) {
                    $points += $this->evaluationStrategy->getTaskPoints($task, $category);
                }

                $dataColumns[] = [
                    self::COL_DEF_LABEL => $series,
                    self::COL_DEF_LIMIT => $points,
                    self::COL_ALIAS => self::DATA_PREFIX . count($dataColumns),
                ];
            }
            $dataColumns[] = [
                self::COL_DEF_LABEL => self::LABEL_PERCENTAGE,
                self::COL_DEF_LIMIT => 100,
                self::COL_ALIAS => self::ALIAS_PERCENTAGE,
            ];
            $dataColumns[] = [
                self::COL_DEF_LABEL => self::LABEL_TOTAL_PERCENTAGE,
                self::COL_DEF_LIMIT => $studentPilnySumLimit != 0 ? round(100 * $sumLimit / $studentPilnySumLimit) : 0,
                self::COL_ALIAS => self::ALIAS_TOTAL_PERCENTAGE,
            ];
            $dataColumns[] = [
                self::COL_DEF_LABEL => self::LABEL_SUM,
                self::COL_DEF_LIMIT => $sumLimit,
                self::COL_ALIAS => self::ALIAS_SUM,
            ];
            $this->dataColumns[$category->value] = $dataColumns;
        }
        return $this->dataColumns[$category->value];
    }

    public function getSeries(): array
    {
        return $this->series;
    }

    /**
     * @param int[] $series
     */
    public function setSeries(array $series): void
    {
        $this->series = $series;
        // invalidate cache of columns
        $this->dataColumns = [];
    }

    /**
     * @return ModelCategory[]
     */
    public function getCategories(): array
    {
        return $this->evaluationStrategy->getCategories();
    }

    protected function composeQuery(ModelCategory $category): string
    {
        if (!$this->series) {
            throw new InvalidStateException('Series not set.');
        }

        $select = [];
        $select[] = "IF(p.display_name IS NULL, CONCAT(p.other_name, ' ', p.family_name), p.display_name) AS `" .
            self::DATA_NAME . '`';
        $select[] = 'sch.name_abbrev AS `' . self::DATA_SCHOOL . '`';

        $sum = $this->evaluationStrategy->getSumColumn();
        $i = 0;
        foreach ($this->getSeries() as $series) {
            $select[] = 'round(SUM(IF(t.series = ' . $series . ', ' . $sum . ", null))) AS '" . self::DATA_PREFIX . $i .
                "'";
            $i += 1;
        }

        $studentPilnySumLimit = $this->getSumLimitForStudentPilny();
        $studentPilnySumLimitInversed = $studentPilnySumLimit != 0 ? 1.0 / $studentPilnySumLimit : 0;

        $select[] = "round(100 * SUM($sum) / SUM("
            . $this->evaluationStrategy->getTaskPointsColumn($category)
            . ")) AS '" . self::ALIAS_PERCENTAGE . "'";
        $select[] = "round(100 * SUM($sum) * " . $studentPilnySumLimitInversed . ") AS '" .
            self::ALIAS_TOTAL_PERCENTAGE . "'";
        $select[] = "round(SUM($sum)) AS '" . self::ALIAS_SUM . "'";
        $select[] = 'ct.contestant_id';

        $from = ' from v_contestant ct
left join person p using(person_id)
left join school sch using(school_id)
left join task t ON t.year = ct.year AND t.contest_id = ct.contest_id
left join submit s ON s.task_id = t.task_id AND s.contestant_id = ct.contestant_id';

        $conditions = [
            'ct.year' => $this->contestYear->year,
            'ct.contest_id' => $this->contestYear->contest_id,
            't.series' => $this->getSeries(),
            'ct.study_year' => $this->evaluationStrategy->categoryToStudyYears($category),
        ];

        $query = 'select ' . implode(', ', $select);
        $query .= $from;

        $where = $this->conditionsToWhere($conditions);
        $query .= " where $where";

        $query .= ' group by p.person_id, sch.name_abbrev '; //abuse MySQL misimplementation of GROUP BY
        $query .= ' order by `' . self::ALIAS_SUM . '` DESC, p.family_name ASC, p.other_name ASC';

        $dataAlias = 'data';
        return "select $dataAlias.*, @rownum := @rownum + 1, @rank := IF($dataAlias." . self::ALIAS_SUM .
            " = @prevSum or ($dataAlias." . self::ALIAS_SUM . ' is null and @prevSum is null), @rank, @rownum) AS `' .
            self::DATA_RANK_FROM . "`, @prevSum := $dataAlias." . self::ALIAS_SUM . "
        from ($query) data, (select @rownum := 0, @rank := 0, @prevSum := -1) init";
    }

    /**
     * Returns total points of Student Pilny (without multiplication for first two tasks) for given series
     *
     * @return int sum of Student Pilny points
     */
    private function getSumLimitForStudentPilny(): int
    {
        return $this->getSumLimit(ModelCategory::tryFrom(ModelCategory::FYKOS_4));
    }

    /**
     * Returns total points for given category and series
     * @return int sum of points
     */
    private function getSumLimit(ModelCategory $category): int
    {
        $sum = 0;
        foreach ($this->getSeries() as $series) {
            // sum points as sum of tasks
            $points = null;
            /** @var TaskModel $task */
            foreach ($this->getTasks($series) as $task) {
                $points += $this->evaluationStrategy->getTaskPoints($task, $category);
            }
            $sum += $points;
        }
        return $sum;
    }
}
