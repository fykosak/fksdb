<?php

declare(strict_types=1);

namespace FKSDB\Models\Results;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Services\TaskService;
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
use FKSDB\Models\WebService\XMLNodeSerializer;
use Fykosak\NetteORM\Model;
use Nette\Application\BadRequestException;
use Nette\InvalidArgumentException;
use Nette\SmartObject;
use Tracy\Debugger;

class ResultsModelFactory implements XMLNodeSerializer
{
    use SmartObject;

    private TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * @throws BadRequestException
     */
    public function createCumulativeResultsModel(ContestYearModel $contestYear): CumulativeResultsModel
    {
        return new CumulativeResultsModel(
            $contestYear,
            $this->taskService,
            self::findEvaluationStrategy($contestYear)
        );
    }

    /**
     * @throws BadRequestException
     */
    public function createDetailResultsModel(ContestYearModel $contestYear): DetailResultsModel
    {
        return new DetailResultsModel(
            $contestYear,
            $this->taskService,
            self::findEvaluationStrategy($contestYear)
        );
    }

    /**
     * @throws BadRequestException
     */
    public function createBrojureResultsModel(ContestYearModel $contestYear): BrojureResultsModel
    {
        return new BrojureResultsModel(
            $contestYear,
            $this->taskService,
            self::findEvaluationStrategy($contestYear)
        );
    }

    /**
     * @throws BadRequestException
     * @deprecated
     */
    public function createSchoolCumulativeResultsModel(ContestYearModel $contestYear): SchoolCumulativeResultsModel
    {
        return new SchoolCumulativeResultsModel(
            $this->createCumulativeResultsModel($contestYear),
            $contestYear,
            $this->taskService
        );
    }

    /**
     * @throws BadRequestException
     */
    public static function findEvaluationStrategy(ContestYearModel $contestYear): EvaluationStrategy
    {
        switch ($contestYear->contest_id) {
            case ContestModel::ID_FYKOS:
                if ($contestYear->year >= 25) {
                    return new EvaluationFykos2011();
                } else {
                    return new EvaluationFykos2001();
                }
            case ContestModel::ID_VYFUK:
                if ($contestYear->year >= 4) {
                    return new EvaluationVyfuk2014();
                } elseif ($contestYear->year >= 2) {
                    return new EvaluationVyfuk2012();
                } else {
                    return new EvaluationVyfuk2011();
                }
        }
        throw new BadRequestException(
            \sprintf('No evaluation strategy found for %s. of %s', $contestYear->year, $contestYear->contest->name)
        );
    }

    /**
     * @param AbstractResultsModel $dataSource
     * @throws \SoapFault
     * @throws BadTypeException
     */
    public function fillNode($dataSource, \DOMNode $node, \DOMDocument $doc, int $formatVersion): void
    {
        if (!$dataSource instanceof AbstractResultsModel) {
            throw new BadTypeException(Model::class, $dataSource);
        }

        if ($formatVersion !== self::EXPORT_FORMAT_1) {
            throw new InvalidArgumentException(\sprintf('Export format %s not supported.', $formatVersion));
        }

        try {
            foreach ($dataSource->getCategories() as $category) {
                // category node
                $categoryNode = $doc->createElement('category');
                $node->appendChild($categoryNode);
                $categoryNode->setAttribute('id', $category->value);

                $columnDefsNode = $doc->createElement('column-definitions');
                $categoryNode->appendChild($columnDefsNode);

                // columns definitions
                foreach ($dataSource->getDataColumns($category) as $column) {
                    $columnDefNode = $doc->createElement('column-definition');
                    $columnDefsNode->appendChild($columnDefNode);

                    $columnDefNode->setAttribute('label', (string)$column[AbstractResultsModel::COL_DEF_LABEL]);
                    $columnDefNode->setAttribute('limit', (string)$column[AbstractResultsModel::COL_DEF_LIMIT]);
                }

                // data
                $dataNode = $doc->createElement('data');
                $categoryNode->appendChild($dataNode);

                // data for each contestant
                foreach ($dataSource->getData($category) as $row) {
                    $contestantNode = $doc->createElement('contestant');
                    $dataNode->appendChild($contestantNode);

                    $contestantNode->setAttribute('name', (string)$row[AbstractResultsModel::DATA_NAME]);
                    $contestantNode->setAttribute('school', (string)$row[AbstractResultsModel::DATA_SCHOOL]);
                    // rank
                    $rankNode = $doc->createElement('rank');
                    $contestantNode->appendChild($rankNode);
                    $rankNode->setAttribute('from', (string)$row[AbstractResultsModel::DATA_RANK_FROM]);
                    if (
                        isset($row[AbstractResultsModel::DATA_RANK_TO])
                        && $row[AbstractResultsModel::DATA_RANK_FROM] != $row[AbstractResultsModel::DATA_RANK_TO]
                    ) {
                        $rankNode->setAttribute('to', (string)$row[AbstractResultsModel::DATA_RANK_TO]);
                    }

                    // data columns
                    foreach ($dataSource->getDataColumns($category) as $column) {
                        $columnNode = $doc->createElement(
                            'column',
                            (string)$row[$column[AbstractResultsModel::COL_ALIAS]]
                        );
                        $contestantNode->appendChild($columnNode);
                    }
                }
            }
        } catch (\Throwable $exception) {
            Debugger::log($exception);
            throw new \SoapFault('Receiver', 'Internal error.');
        }
    }
}
