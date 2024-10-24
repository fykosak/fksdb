<?php

declare(strict_types=1);

namespace FKSDB\Models\Results\Models;

use FKSDB\Models\ORM\Models\ContestCategoryModel;
use FKSDB\Models\ORM\Models\TaskModel;
use Nette\InvalidStateException;

/**
 * Detailed results of a single series. Number of tasks is dynamic.
 */
class BrojureResultsModel extends AbstractResultsModel
{
    public const COL_SERIES_PREFIX = 's';
    /** @phpstan-var int[] */
    protected array $series;
    /**
     * Number of (single) series that is listed in detail
     * @var int
     */
    protected int $listedSeries;
    /**
     * @phpstan-var array<string,array<int,array{label:string,limit:float|int|null,alias:string}>>
     */
    private array $dataColumns = [];

    /**
     * Definition of header.
     * @phpstan-return array<int,array{label:string,limit:float|int|null,alias:string}>
     */
    public function getDataColumns(ContestCategoryModel $category): array
    {
        if (!isset($this->dataColumns[$category->label])) {
            $dataColumns = [];
            $sumLimit = $this->getSumLimit($category);
            $studentPilnySumLimit = $this->getSumLimitForStudentPilny();
            /** @var TaskModel $task */
            foreach ($this->getTasks($this->listedSeries) as $task) {
                $dataColumns[] = [
                    self::COL_DEF_LABEL => $task->label,
                    self::COL_DEF_LIMIT => $this->evaluationStrategy->getTaskPoints($task, $category),
                    self::COL_ALIAS => self::DATA_PREFIX . count($dataColumns),
                ];
            }

            foreach ($this->getSeries() as $series) {
                $points = null;
                /** @var TaskModel $task */
                foreach ($this->getTasks($series) as $task) {
                    $points += $this->evaluationStrategy->getTaskPoints($task, $category);
                }
                $dataColumns[] = [
                    self::COL_DEF_LABEL => self::COL_SERIES_PREFIX . $series,
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
            $this->dataColumns[$category->label] = $dataColumns;
        }
        return $this->dataColumns[$category->label];
    }

    /**
     * @phpstan-return int[]
     */
    public function getSeries(): array
    {
        return $this->series;
    }

    /**
     * @phpstan-param int[] $series
     */
    public function setSeries(array $series): void
    {
        $this->series = $series;
        // invalidate cache of columns
        $this->dataColumns = [];
    }

    public function getListedSeries(): int
    {
        return $this->listedSeries;
    }

    public function setListedSeries(int $listedSeries): void
    {
        $this->listedSeries = $listedSeries;
        // invalidate cache of columns
        $this->dataColumns = [];
    }

    /**
     * @return literal-string
     */
    protected function composeQuery(ContestCategoryModel $category): string
    {
        if (!$this->series) {
            throw new InvalidStateException('Series not set.');
        }
        if (!in_array($this->listedSeries, $this->series)) {
            throw new InvalidStateException('Listed series is not among series.');
        }

        $select = [];
        $select[] = "IF(p.display_name IS NULL, CONCAT(p.other_name, ' ', p.family_name), p.display_name) AS `"
            . self::DATA_NAME . '`';
        $select[] = 'sch.name_abbrev AS `' . self::DATA_SCHOOL . '`';

        $tasks = $this->getTasks($this->listedSeries);
        $i = 0;
        /** @var TaskModel $task */
        foreach ($tasks as $task) {
            $points = $this->evaluationStrategy->getPointsColumn($task);
            $select[] = 'round(MAX(IF(t.task_id = ' . $task->task_id . ', '
                . $points . ", null))) AS '" . self::DATA_PREFIX . $i . "'";
            $i += 1;
        }

        $sum = $this->evaluationStrategy->getSumColumn();
        foreach ($this->getSeries() as $series) {
            $select[] = 'round(SUM(IF(t.series = '
                . $series . ', ' . $sum . ", null))) AS '"
                . self::DATA_PREFIX . $i . "'";
            $i += 1;
        }

        $studentPilnySumLimit = $this->getSumLimitForStudentPilny();
        $studentPilnySumLimitInversed = $studentPilnySumLimit != 0 ? 1.0 / $studentPilnySumLimit : 0;

        $select[] = "round(100 * SUM($sum) / SUM("
            . $this->evaluationStrategy->getTaskPointsColumn($category)
            . ")) AS '" . self::ALIAS_PERCENTAGE . "'";
        $select[] = "round(100 * SUM($sum) * "
            . $studentPilnySumLimitInversed
            . ") AS '"
            . self::ALIAS_TOTAL_PERCENTAGE
            . "'";
        $select[] = "round(SUM($sum)) AS '" . self::ALIAS_SUM . "'";

        $query = 'select ' . implode(', ', $select);
        $query .= ' from v_contestant ct
left join person p using(person_id)
left join school sch using(school_id)
left join task t ON t.year = ct.year AND t.contest_id = ct.contest_id
left join submit s ON s.task_id = t.task_id AND s.contestant_id = ct.contestant_id';

        $where = $this->conditionsToWhere([
            'ct.year' => $this->contestYear->year,
            'ct.contest_id' => $this->contestYear->contest_id,
            't.series' => $this->getSeries(),
            'ct.study_year_new' => $this->evaluationStrategy->categoryToStudyYears($category),
        ]);
        $query .= " where $where";

        $query .= ' group by p.person_id, sch.name_abbrev '; //abuse MySQL misimplementation of GROUP BY
        $query .= ' order by `' . self::ALIAS_SUM . '` DESC, p.family_name ASC, p.other_name ASC';

        $dataAlias = 'data';
        /** @phpstan-ignore-next-line */
        return "select $dataAlias.*, @rownum := @rownum + 1, @rank := IF($dataAlias."
            . self::ALIAS_SUM . " = @prevSum or ($dataAlias." . self::ALIAS_SUM
            . ' is null and @prevSum is null), @rank, @rownum) AS `' . self::DATA_RANK_FROM
            . "`, @prevSum := $dataAlias." . self::ALIAS_SUM . "
        from ($query) data, (select @rownum := 0, @rank := 0, @prevSum := -1) init";
    }

    /**
     * Returns total points of Student Pilny (without multiplication for first two tasks) for given series
     *
     * @return float sum of Student Pilny points
     */
    private function getSumLimitForStudentPilny(): float
    {
        return $this->getSumLimit($this->contestCategoryService->findByLabel(ContestCategoryModel::FYKOS_4));
    }

    /**
     * Returns total points for given category and series
     * @return float sum of points
     */
    private function getSumLimit(ContestCategoryModel $category): float
    {
        $sum = 0;
        foreach ($this->getSeries() as $series) {
            /** @var TaskModel $task */
            foreach ($this->getTasks($series) as $task) {
                $sum += $this->evaluationStrategy->getTaskPoints($task, $category);
            }
        }
        return $sum;
    }
}
