<?php

declare(strict_types=1);

namespace FKSDB\Models\Results\Models;

use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Services\TaskService;
use FKSDB\Models\Results\EvaluationStrategies\EvaluationNullObject;
use FKSDB\Models\Results\ModelCategory;
use Nette\Database\Connection;
use Nette\Database\Row;
use Nette\InvalidStateException;
use Nette\NotSupportedException;

/**
 * Cumulative results of schools' contest.
 */
class SchoolCumulativeResultsModel extends AbstractResultsModel
{

    protected array $series;
    /**
     * Cache
     */
    private array $dataColumns = [];
    private CumulativeResultsModel $cumulativeResultsModel;

    public function __construct(
        CumulativeResultsModel $cumulativeResultsModel,
        ContestYearModel $contestYear,
        TaskService $taskService,
        Connection $connection
    ) {
        parent::__construct($contestYear, $taskService, $connection, new EvaluationNullObject());
        $this->cumulativeResultsModel = $cumulativeResultsModel;
    }

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
            foreach ($this->getSeries() as $series) {
                $dataColumns[] = [
                    self::COL_DEF_LABEL => $series,
                    self::COL_DEF_LIMIT => 0, //not well defined
                    self::COL_ALIAS => self::DATA_PREFIX . count($dataColumns),
                ];
            }
            $dataColumns[] = [
                self::COL_DEF_LABEL => self::LABEL_CONTESTANTS_COUNT,
                self::COL_DEF_LIMIT => 0, //not well defined
                self::COL_ALIAS => self::ALIAS_CONTESTANTS_COUNT,
            ];
            $dataColumns[] = [
                self::COL_DEF_LABEL => self::LABEL_UNWEIGHTED_SUM,
                self::COL_DEF_LIMIT => 0, //not well defined
                self::COL_ALIAS => self::ALIAS_UNWEIGHTED_SUM,
            ];
            $dataColumns[] = [
                self::COL_DEF_LABEL => self::LABEL_PERCENTAGE,
                self::COL_DEF_LIMIT => 100,
                self::COL_ALIAS => self::ALIAS_PERCENTAGE,
            ];
            $dataColumns[] = [
                self::COL_DEF_LABEL => self::LABEL_SUM,
                self::COL_DEF_LIMIT => 0, //not well defined
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
        $this->cumulativeResultsModel->setSeries($series);
        // invalidate cache of columns
        $this->dataColumns = [];
    }

    /**
     * @return ModelCategory[]
     */
    public function getCategories(): array
    {
        //return $this->evaluationStrategy->getCategories();
        return [
            ModelCategory::tryFrom(ModelCategory::CAT_ALL),
        ];
    }

    protected function composeQuery(ModelCategory $category): string
    {
        throw new NotSupportedException();
    }

    /**
     * @return Row[]
     */
    public function getData(ModelCategory $category): array
    {
        $categories = [];
        if ($category->value == ModelCategory::CAT_ALL) {
            $categories = $this->cumulativeResultsModel->getCategories();
        } else {
            $categories[] = $category;
        }

        $data = [];
        foreach ($categories as $cummulativeCategory) {
            foreach ($this->cumulativeResultsModel->getData($cummulativeCategory) as $row) {
                $schoolName = $row[self::DATA_SCHOOL];
                $contestant = $row;
                unset($contestant[self::DATA_NAME]);
                unset($contestant[self::DATA_SCHOOL]);
                unset($contestant[self::DATA_RANK_FROM]);
                unset($contestant[self::DATA_RANK_TO]);
                $data[$schoolName][] = $contestant;
            }
        }
        $result = [];
        foreach ($data as $schoolName => $dataRow) {
            usort($dataRow, fn($a, $b) => ($a[self::ALIAS_SUM] > $b[self::ALIAS_SUM]) ? -1 : 1);
            $resultRow = $this->createResultRow($dataRow, $category);
            $resultRow[self::DATA_NAME] = $schoolName;
            $resultRow[self::DATA_SCHOOL] = $schoolName;
            $result[] = $resultRow;
        }
        usort($result, fn($a, $b) => ($a[self::ALIAS_UNWEIGHTED_SUM] > $b[self::ALIAS_UNWEIGHTED_SUM]) ? -1 : 1);

        $prevSum = false;
        for ($i = 0; $i < count($result); $i++) {
            if ($result[$i][self::ALIAS_UNWEIGHTED_SUM] !== $prevSum) {
                $result[$i][self::DATA_RANK_FROM] = $i + 1;
            } else {
                $result[$i][self::DATA_RANK_FROM] = $result[$i - 1][self::DATA_RANK_FROM];
            }
            $prevSum = $result[$i][self::ALIAS_UNWEIGHTED_SUM];
        }

        // reverse iteration to get ranking ranges
        $nextSum = false; //because last sum can be null
        for ($i = count($result) - 1; $i >= 0; --$i) {
            if ($result[$i][self::ALIAS_UNWEIGHTED_SUM] !== $nextSum) {
                $result[$i][self::DATA_RANK_TO] = $i + 1;
            } else {
                $result[$i][self::DATA_RANK_TO] = $result[$i + 1][self::DATA_RANK_TO];
            }
            $nextSum = $result[$i][self::ALIAS_UNWEIGHTED_SUM];
        }

        return $result;
    }

    private function weightVector(int $i): float
    {
        return max([1.0 - 0.1 * $i, 0.1]);
    }

    private function createResultRow(array $schoolContestants, ModelCategory $category): array
    {
        $resultRow = [];
        foreach ($this->getDataColumns($category) as $column) {
            $resultRow[$column[self::COL_ALIAS]] = 0;
        }

        $resultRow[self::ALIAS_CONTESTANTS_COUNT] = 0;

        for ($i = 0; $i < count($schoolContestants); $i++) {
            if ($schoolContestants[$i][self::ALIAS_SUM] != 0) {
                $resultRow[self::ALIAS_CONTESTANTS_COUNT]++;
            }
            foreach ($schoolContestants[$i] as $column => $value) {
                switch ($column) {
                    case self::ALIAS_PERCENTAGE:
                        $resultRow[$column] += $value;
                        break;
                    default:
                        if (isset($resultRow[$column])) {
                            $resultRow[$column] += $this->weightVector($i) * $value;
                        }
                        break;
                }
            }
            $resultRow[self::ALIAS_UNWEIGHTED_SUM] += $schoolContestants[$i][self::ALIAS_SUM];
        }
        $resultRow[self::ALIAS_PERCENTAGE] = ($resultRow[self::ALIAS_CONTESTANTS_COUNT] > 0) ? round(
            $resultRow[self::ALIAS_PERCENTAGE] / (float)$resultRow[self::ALIAS_CONTESTANTS_COUNT]
        ) : null;
        foreach ($resultRow as $key => $value) {
            if (is_float($value)) {
                $resultRow[$key] = round($value);
            }
            if ($value == 0) {
                $resultRow[$key] = null;
            }
        }
        return $resultRow;
    }
}
