<?php

namespace FKSDB\Results\Models;

use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Services\ServiceTask;
use FKSDB\Results\EvaluationStrategies\EvaluationNullObject;
use FKSDB\Results\ModelCategory;
use Nette\Database\Connection;
use Nette\InvalidStateException;

/**
 * Cumulative results of schools' contest.
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class SchoolCumulativeResultsModel extends AbstractResultsModel {

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
     *
     * @var CumulativeResultsModel
     */
    private $cumulativeResultsModel;

    /**
     * FKSDB\Results\Models\SchoolCumulativeResultsModel constructor.
     * @param CumulativeResultsModel $cumulativeResultsModel
     * @param ModelContest $contest
     * @param ServiceTask $serviceTask
     * @param Connection $connection
     * @param $year
     */
    public function __construct(CumulativeResultsModel $cumulativeResultsModel, ModelContest $contest, ServiceTask $serviceTask, Connection $connection, $year) {
        parent::__construct($contest, $serviceTask, $connection, $year, new EvaluationNullObject());
        $this->cumulativeResultsModel = $cumulativeResultsModel;
    }

    /**
     * Definition of header.
     *
     * @param ModelCategory $category
     * @return array
     */
    public function getDataColumns(ModelCategory $category) {
        if ($this->series === null) {
            throw new InvalidStateException('Series not specified.');
        }
        if (!isset($this->dataColumns[$category->id])) {
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
                self::COL_DEF_LABEL => self::LABEL_PERCETAGE,
                self::COL_DEF_LIMIT => 100,
                self::COL_ALIAS => self::ALIAS_PERCENTAGE,
            ];
            $dataColumns[] = [
                self::COL_DEF_LABEL => self::LABEL_SUM,
                self::COL_DEF_LIMIT => 0, //not well defined
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
        $this->cumulativeResultsModel->setSeries($series);
        // invalidate cache of columns
        $this->dataColumns = [];
    }

    /**
     * @return array
     */
    public function getCategories() {
        //return $this->evaluationStrategy->getCategories();
        return [
            new ModelCategory(ModelCategory::CAT_ALL)
        ];
    }

    /**
     * @param ModelCategory $category
     * @return mixed|void
     */
    protected function composeQuery(ModelCategory $category) {
        throw new \Nette\NotSupportedException;
    }

    /**
     * @param ModelCategory $category
     * @return array of Nette\Database\Row
     */
    public function getData(ModelCategory $category) {
        $categories = [];
        if ($category->id == ModelCategory::CAT_ALL) {
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
            usort($dataRow, function ($a, $b) {
                return ($a[self::ALIAS_SUM] > $b[self::ALIAS_SUM]) ? -1 : 1;
            });
            $resultRow = $this->createResultRow($dataRow, $category);
            $resultRow[self::DATA_NAME] = $schoolName;
            $resultRow[self::DATA_SCHOOL] = $schoolName;
            $result[] = $resultRow;
        }
        usort($result, function ($a, $b) {
            return ($a[self::ALIAS_SUM] > $b[self::ALIAS_SUM]) ? -1 : 1;
        });

        $prevSum = false;
        for ($i = 0; $i < count($result); $i++) {
            if ($result[$i][self::ALIAS_SUM] !== $prevSum) {
                $result[$i][self::DATA_RANK_FROM] = $i + 1;
            } else {
                $result[$i][self::DATA_RANK_FROM] = $result[$i - 1][self::DATA_RANK_FROM];
            }
            $prevSum = $result[$i][self::ALIAS_SUM];
        }

        // reverse iteration to get ranking ranges
        $nextSum = false; //because last sum can be null
        for ($i = count($result) - 1; $i >= 0; --$i) {
            if ($result[$i][self::ALIAS_SUM] !== $nextSum) {
                $result[$i][self::DATA_RANK_TO] = $i + 1;
            } else {
                $result[$i][self::DATA_RANK_TO] = $result[$i + 1][self::DATA_RANK_TO];
            }
            $nextSum = $result[$i][self::ALIAS_SUM];
        }

        return $result;
    }

    //TODO better have somehow in evaluation strategy

    /**
     * @param $i
     * @return mixed
     */
    private function weightVector($i) {
        return max([1.0 - 0.1 * $i, 0.1]);
    }

    /**
     * @param $schoolContestants
     * @param ModelCategory $category
     * @return array
     */
    private function createResultRow($schoolContestants, ModelCategory $category) {
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
        $resultRow[self::ALIAS_PERCENTAGE] = ($resultRow[self::ALIAS_CONTESTANTS_COUNT] > 0) ? round($resultRow[self::ALIAS_PERCENTAGE] / (float)$resultRow[self::ALIAS_CONTESTANTS_COUNT]) : null;
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
