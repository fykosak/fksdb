<?php

use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Services\ServiceTask;
use Nette\Database\Connection;
use Nette\Diagnostics\Debugger;
use Nette\InvalidArgumentException;
use Nette\Object;
use WebService\IXMLNodeSerializer;

/**
 * Description of ResultsModelFactory
 *
 * @author michal
 */
class ResultsModelFactory extends Object implements IXMLNodeSerializer {

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ServiceTask
     */
    private $serviceTask;

    /**
     * ResultsModelFactory constructor.
     * @param Connection $connection
     * @param ServiceTask $serviceTask
     */
    public function __construct(Connection $connection, ServiceTask $serviceTask) {
        $this->connection = $connection;
        $this->serviceTask = $serviceTask;
    }

    /**
     *
     * @param \FKSDB\ORM\Models\ModelContest $contest
     * @param int $year
     * @return IResultsModel
     */
    public function createCumulativeResultsModel(ModelContest $contest, $year) {
        $evaluationStrategy = self::findEvaluationStrategy($contest, $year);
        if ($evaluationStrategy === null) {
            throw new InvalidArgumentException('Undefined results model for ' . $contest->name . '@' . $year);
        }
        return new CumulativeResultsModel($contest, $this->serviceTask, $this->connection, $year, $evaluationStrategy);
    }

    /**
     *
     * @param \FKSDB\ORM\Models\ModelContest $contest
     * @param int $year
     * @return IResultsModel
     */
    public function createDetailResultsModel(ModelContest $contest, $year) {
        $evaluationStrategy = self::findEvaluationStrategy($contest, $year);
        if ($evaluationStrategy === null) {
            throw new InvalidArgumentException('Undefined results model for ' . $contest->name . '@' . $year);
        }
        return new DetailResultsModel($contest, $this->serviceTask, $this->connection, $year, $evaluationStrategy);
    }

    /**
     *
     * @param \FKSDB\ORM\Models\ModelContest $contest
     * @param int $year
     * @return IResultsModel
     */
    public function createBrojureResultsModel(ModelContest $contest, $year) {
        $evaluationStrategy = self::findEvaluationStrategy($contest, $year);
        if ($evaluationStrategy === null) {
            throw new InvalidArgumentException('Undefined results model for ' . $contest->name . '@' . $year);
        }
        return new BrojureResultsModel($contest, $this->serviceTask, $this->connection, $year, $evaluationStrategy);
    }

    /**
     *
     * @param \FKSDB\ORM\Models\ModelContest $contest
     * @param int $year
     * @return IResultsModel
     */
    public function createSchoolCumulativeResultsModel(ModelContest $contest, $year) {
        $cumulativeResultsModel = $this->createCumulativeResultsModel($contest, $year);
        return new SchoolCumulativeResultsModel($cumulativeResultsModel, $contest, $this->serviceTask, $this->connection, $year);
    }

    /**
     *
     * @param \FKSDB\ORM\Models\ModelContest $contest
     * @param int $year
     * @return \IEvaluationStrategy|null
     */
    public static function findEvaluationStrategy($contest, $year) {
        if ($contest instanceof ModelContest) {
            $contestId = $contest->contest_id;
        } else {
            $contestId = $contest;
        }
        if ($contestId == ModelContest::ID_FYKOS) {
            if ($year >= 25) {
                return new EvaluationFykos2011();
            } else {
                return new EvaluationFykos2001();
            }
        } else if ($contestId == ModelContest::ID_VYFUK) {
            if ($year >= 4) {
                return new EvaluationVyfuk2014();
            } else if ($year >= 2) {
                return new EvaluationVyfuk2012();
            } else {
                return new EvaluationVyfuk2011();
            }
        }
        return null;
    }

    /**
     * @param $dataSource
     * @param DOMNode $node
     * @param DOMDocument $doc
     * @param $format
     * @return mixed|void
     * @throws SoapFault
     */
    public function fillNode($dataSource, DOMNode $node, DOMDocument $doc, $format) {
        if (!$dataSource instanceof IResultsModel) {
            throw new InvalidArgumentException('Expected IResultsModel, got ' . get_class($dataSource) . '.');
        }

        if ($format !== self::EXPORT_FORMAT_1) {
            throw new InvalidArgumentException(sprintf('Export format %s not supported.', $format));
        }

        try {
            foreach ($dataSource->getCategories() as $category) {
                // category node
                $categoryNode = $doc->createElement('category');
                $node->appendChild($categoryNode);
                $categoryNode->setAttribute('id', $category->id);

                $columnDefsNode = $doc->createElement('column-definitions');
                $categoryNode->appendChild($columnDefsNode);

                // columns definitions
                foreach ($dataSource->getDataColumns($category) as $column) {
                    $columnDefNode = $doc->createElement('column-definition');
                    $columnDefsNode->appendChild($columnDefNode);

                    $columnDefNode->setAttribute('label', $column[IResultsModel::COL_DEF_LABEL]);
                    $columnDefNode->setAttribute('limit', $column[IResultsModel::COL_DEF_LIMIT]);
                }

                // data
                $dataNode = $doc->createElement('data');
                $categoryNode->appendChild($dataNode);

                // data for each contestant
                foreach ($dataSource->getData($category) as $row) {
                    $contestantNode = $doc->createElement('contestant');
                    $dataNode->appendChild($contestantNode);

                    $contestantNode->setAttribute('name', $row[IResultsModel::DATA_NAME]);
                    $contestantNode->setAttribute('school', $row[IResultsModel::DATA_SCHOOL]);
                    // rank
                    $rankNode = $doc->createElement('rank');
                    $contestantNode->appendChild($rankNode);
                    $rankNode->setAttribute('from', $row[IResultsModel::DATA_RANK_FROM]);
                    if (isset($row[IResultsModel::DATA_RANK_TO]) && $row[IResultsModel::DATA_RANK_FROM] != $row[IResultsModel::DATA_RANK_TO]) {
                        $rankNode->setAttribute('to', $row[IResultsModel::DATA_RANK_TO]);
                    }

                    // data columns
                    foreach ($dataSource->getDataColumns($category) as $column) {
                        $columnNode = $doc->createElement('column', $row[$column[IResultsModel::COL_ALIAS]]);
                        $contestantNode->appendChild($columnNode);
                    }
                }
            }
        } catch (Exception $exception) {
            Debugger::log($exception);
            throw new SoapFault('Receiver', 'Internal error.');
        }
    }

}


