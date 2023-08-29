<?php

declare(strict_types=1);

namespace FKSDB\Models\Results;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\Results\EvaluationStrategies\EvaluationFykos2001;
use FKSDB\Models\Results\EvaluationStrategies\EvaluationFykos2011;
use FKSDB\Models\Results\EvaluationStrategies\EvaluationFykos2023;
use FKSDB\Models\Results\EvaluationStrategies\EvaluationStrategy;
use FKSDB\Models\Results\EvaluationStrategies\EvaluationVyfuk2011;
use FKSDB\Models\Results\EvaluationStrategies\EvaluationVyfuk2012;
use FKSDB\Models\Results\EvaluationStrategies\EvaluationVyfuk2014;
use FKSDB\Models\Results\EvaluationStrategies\EvaluationVyfuk2023;
use FKSDB\Models\Results\Models\AbstractResultsModel;
use FKSDB\Models\Results\Models\BrojureResultsModel;
use FKSDB\Models\Results\Models\CumulativeResultsModel;
use FKSDB\Models\Results\Models\DetailResultsModel;
use FKSDB\Models\Results\Models\SchoolCumulativeResultsModel;
use FKSDB\Models\WebService\XMLNodeSerializer;
use Fykosak\NetteORM\Model;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Nette\SmartObject;
use Tracy\Debugger;

/**
 * @phpstan-implements XMLNodeSerializer<AbstractResultsModel>
 */
class ResultsModelFactory implements XMLNodeSerializer
{
    use SmartObject;

    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @throws BadRequestException
     */
    public function createCumulativeResultsModel(ContestYearModel $contestYear): CumulativeResultsModel
    {
        return new CumulativeResultsModel($this->container, $contestYear);
    }

    /**
     * @throws BadRequestException
     */
    public function createDetailResultsModel(ContestYearModel $contestYear): DetailResultsModel
    {
        return new DetailResultsModel($this->container, $contestYear);
    }

    /**
     * @throws BadRequestException
     */
    public function createBrojureResultsModel(ContestYearModel $contestYear): BrojureResultsModel
    {
        return new BrojureResultsModel($this->container, $contestYear);
    }

    /**
     * @throws BadRequestException
     * @deprecated
     */
    public function createSchoolCumulativeResultsModel(ContestYearModel $contestYear): SchoolCumulativeResultsModel
    {
        return new SchoolCumulativeResultsModel(
            $this->container,
            $this->createCumulativeResultsModel($contestYear),
            $contestYear,
        );
    }

    /**
     * @throws BadRequestException
     */
    public static function findEvaluationStrategy(
        Container $container,
        ContestYearModel $contestYear
    ): EvaluationStrategy {
        switch ($contestYear->contest_id) {
            case ContestModel::ID_FYKOS:
                if ($contestYear->year >= 37) {
                    return new EvaluationFykos2023($container, $contestYear);
                } elseif ($contestYear->year >= 25) {
                    return new EvaluationFykos2011($container, $contestYear);
                } else {
                    return new EvaluationFykos2001($container, $contestYear);
                }
            case ContestModel::ID_VYFUK:
                if ($contestYear->year >= 12) {
                    return new EvaluationVyfuk2023($container, $contestYear);
                } elseif ($contestYear->year >= 4) {
                    return new EvaluationVyfuk2014($container, $contestYear);
                } elseif ($contestYear->year >= 2) {
                    return new EvaluationVyfuk2012($container, $contestYear);
                } else {
                    return new EvaluationVyfuk2011($container, $contestYear);
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
                $categoryNode->setAttribute('id', (string)$category->contest_category_id);

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
            var_dump($exception->getMessage());
            throw new \SoapFault('Receiver', 'Internal error.');
        }
    }
}
