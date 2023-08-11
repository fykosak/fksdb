<?php

declare(strict_types=1);

namespace FKSDB\Models\Results\Models;

use FKSDB\Models\ORM\Models\ContestCategoryModel;
use FKSDB\Models\ORM\Models\ContestYearModel;
use Nette\DI\Container;
use Nette\NotSupportedException;

/**
 * Cumulative results of schools' contest.
 * @deprecated
 */
class SchoolCumulativeResultsModel extends AbstractResultsModel
{
    /** @var int[] */
    protected array $series;
    /**
     * @phpstan-var array<string,array<int,array{label:string,limit:float|int|null,alias:string}>>
     */
    private array $dataColumns = [];
    private CumulativeResultsModel $cumulativeResultsModel;

    public function __construct(
        Container $container,
        CumulativeResultsModel $cumulativeResultsModel,
        ContestYearModel $contestYear
    ) {
        parent::__construct($container, $contestYear);
        $this->cumulativeResultsModel = $cumulativeResultsModel;
    }

    /**
     * Definition of header.
     * @phpstan-return array<int,array{label:string,limit:float|int|null,alias:string}>
     */
    public function getDataColumns(ContestCategoryModel $category): array
    {
        if (!isset($this->dataColumns[$category->label])) {
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
            $this->dataColumns[$category->label] = $dataColumns;
        }
        return $this->dataColumns[$category->label];
    }

    /**
     * @return int[]
     */
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
     * @return ContestCategoryModel[]
     */
    public function getCategories(): array
    {
        return [
            $this->contestCategoryService->findByLabel(ContestCategoryModel::ALL),
        ];
    }

    /**
     * @return literal-string
     */
    protected function composeQuery(ContestCategoryModel $category): string
    {
        throw new NotSupportedException();
    }

    public function getData(ContestCategoryModel $category): array
    {
        $categories = [];
        if ($category->label === ContestCategoryModel::ALL) {
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

    private function createResultRow(array $schoolContestants, ContestCategoryModel $category): array
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
