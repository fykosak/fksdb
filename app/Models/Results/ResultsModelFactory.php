<?php

namespace FKSDB\Models\Results;

use DOMDocument;
use DOMNode;
use Exception;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Services\ServiceTask;
use FKSDB\Models\Results\EvaluationStrategies\EvaluationFykos2001;
use FKSDB\Models\Results\EvaluationStrategies\EvaluationFykos2011;
use FKSDB\Models\Results\EvaluationStrategies\EvaluationStrategy;
use FKSDB\Models\Results\EvaluationStrategies\EvaluationVyfuk2011;
use FKSDB\Models\Results\EvaluationStrategies\EvaluationVyfuk2012;
use FKSDB\Models\Results\EvaluationStrategies\EvaluationVyfuk2014;
use FKSDB\Models\Results\Models\AbstractResultsModel;
use FKSDB\Models\Results\Models\BrojureResultsModel;
use FKSDB\Models\Results\Models\CumulativeResultsModel;
use FKSDB\Models\Results\Models\DetailResultsModel;
use FKSDB\Models\Results\Models\SchoolCumulativeResultsModel;
use Nette\Application\BadRequestException;
use Nette\Database\Connection;
use Nette\InvalidArgumentException;
use Nette\SmartObject;
use SoapFault;
use Tracy\Debugger;
use FKSDB\Models\WebService\IXMLNodeSerializer;

/**
 * Description of FKSDB\Results\ResultsModelFactory
 *
 * @author michal
 */
class ResultsModelFactory implements IXMLNodeSerializer {
    use SmartObject;

    private Connection $connection;
    private ServiceTask $serviceTask;

    public function __construct(Connection $connection, ServiceTask $serviceTask) {
        $this->connection = $connection;
        $this->serviceTask = $serviceTask;
    }

    /**
     *
     * @param ModelContest $contest
     * @param int $year
     * @return CumulativeResultsModel
     * @throws BadRequestException
     */
    public function createCumulativeResultsModel(ModelContest $contest, int $year): CumulativeResultsModel {
        $evaluationStrategy = self::findEvaluationStrategy($contest, $year);
        if ($evaluationStrategy === null) {
            throw new InvalidArgumentException('Undefined results model for ' . $contest->name . '@' . $year);
        }
        return new CumulativeResultsModel($contest, $this->serviceTask, $this->connection, $year, $evaluationStrategy);
    }

    /**
     *
     * @param ModelContest $contest
     * @param int $year
     * @return DetailResultsModel
     * @throws BadRequestException
     */
    public function createDetailResultsModel(ModelContest $contest, int $year): DetailResultsModel {
        $evaluationStrategy = self::findEvaluationStrategy($contest, $year);
        if ($evaluationStrategy === null) {
            throw new InvalidArgumentException('Undefined results model for ' . $contest->name . '@' . $year);
        }
        return new DetailResultsModel($contest, $this->serviceTask, $this->connection, $year, $evaluationStrategy);
    }

    /**
     *
     * @param ModelContest $contest
     * @param int $year
     * @return BrojureResultsModel
     * @throws BadRequestException
     */
    public function createBrojureResultsModel(ModelContest $contest, int $year): BrojureResultsModel {
        $evaluationStrategy = self::findEvaluationStrategy($contest, $year);
        if ($evaluationStrategy === null) {
            throw new InvalidArgumentException('Undefined results model for ' . $contest->name . '@' . $year);
        }
        return new BrojureResultsModel($contest, $this->serviceTask, $this->connection, $year, $evaluationStrategy);
    }

    /**
     *
     * @param ModelContest $contest
     * @param int $year
     * @return SchoolCumulativeResultsModel
     * @throws BadRequestException
     */
    public function createSchoolCumulativeResultsModel(ModelContest $contest, int $year): SchoolCumulativeResultsModel {
        $cumulativeResultsModel = $this->createCumulativeResultsModel($contest, $year);
        return new SchoolCumulativeResultsModel($cumulativeResultsModel, $contest, $this->serviceTask, $this->connection, $year);
    }

    /**
     *
     * @param ModelContest|int $contest
     * @param int $year
     * @return EvaluationStrategy
     * @throws BadRequestException
     */
    public static function findEvaluationStrategy($contest, int $year): EvaluationStrategy {
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
        } elseif ($contestId == ModelContest::ID_VYFUK) {
            if ($year >= 4) {
                return new EvaluationVyfuk2014();
            } elseif ($year >= 2) {
                return new EvaluationVyfuk2012();
            } else {
                return new EvaluationVyfuk2011();
            }
        }
        throw new BadRequestException(\sprintf('No evaluation strategy found for %s. of %s', $year, $contest->name));
    }

    /**
     * @param AbstractResultsModel $dataSource
     * @param DOMNode $node
     * @param DOMDocument $doc
     * @param int $formatVersion
     * @return void
     * @throws SoapFault
     * @throws BadTypeException
     */
    public function fillNode($dataSource, DOMNode $node, DOMDocument $doc, int $formatVersion): void {
        if (!$dataSource instanceof AbstractResultsModel) {
            throw new BadTypeException(AbstractModelSingle::class, $dataSource);
        }

        if ($formatVersion !== self::EXPORT_FORMAT_1) {
            throw new InvalidArgumentException(\sprintf('Export format %s not supported.', $formatVersion));
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

                    $columnDefNode->setAttribute('label', $column[AbstractResultsModel::COL_DEF_LABEL]);
                    $columnDefNode->setAttribute('limit', $column[AbstractResultsModel::COL_DEF_LIMIT]);
                }

                // data
                $dataNode = $doc->createElement('data');
                $categoryNode->appendChild($dataNode);

                // data for each contestant
                foreach ($dataSource->getData($category) as $row) {
                    $contestantNode = $doc->createElement('contestant');
                    $dataNode->appendChild($contestantNode);

                    $contestantNode->setAttribute('name', $row[AbstractResultsModel::DATA_NAME]);
                    $contestantNode->setAttribute('school', $row[AbstractResultsModel::DATA_SCHOOL]);
                    // rank
                    $rankNode = $doc->createElement('rank');
                    $contestantNode->appendChild($rankNode);
                    $rankNode->setAttribute('from', $row[AbstractResultsModel::DATA_RANK_FROM]);
                    if (isset($row[AbstractResultsModel::DATA_RANK_TO]) && $row[AbstractResultsModel::DATA_RANK_FROM] != $row[AbstractResultsModel::DATA_RANK_TO]) {
                        $rankNode->setAttribute('to', $row[AbstractResultsModel::DATA_RANK_TO]);
                    }

                    // data columns
                    foreach ($dataSource->getDataColumns($category) as $column) {
                        $columnNode = $doc->createElement('column', $row[$column[AbstractResultsModel::COL_ALIAS]]);
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
