<?php

declare(strict_types=1);

namespace FKSDB\Models\StoredQuery;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\StoredQuery\ParameterModel;
use FKSDB\Models\ORM\Models\StoredQuery\QueryModel;
use FKSDB\Models\ORM\Services\StoredQuery\QueryService;
use FKSDB\Models\Utils\Utils;
use FKSDB\Models\WebService\XMLNodeSerializer;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use FKSDB\Modules\Core\PresenterTraits\NoContestYearAvailable;
use FKSDB\Modules\OrgModule\BasePresenter;
use Nette\Database\Connection;
use Nette\InvalidArgumentException;

/**
 * @phpstan-implements XMLNodeSerializer<StoredQuery>
 */
class StoredQueryFactory implements XMLNodeSerializer
{

    public const PARAM_CONTEST_ID = 'contest_id';
    public const PARAM_CONTEST = 'contest';
    public const PARAM_YEAR = 'year';
    public const PARAM_SERIES = 'series';
    public const PARAM_AC_YEAR = 'ac_year';
    private Connection $connection;
    private QueryService $storedQueryService;

    public function __construct(Connection $connection, QueryService $storedQueryService)
    {
        $this->connection = $connection;
        $this->storedQueryService = $storedQueryService;
    }

    /**
     * @phpstan-param (ParameterModel|StoredQueryParameter)[] $parameters
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    public function createQueryFromSQL(BasePresenter $presenter, string $sql, array $parameters): StoredQuery
    {
        $storedQuery = StoredQuery::createWithoutQueryPattern($this->connection, $sql, $parameters);
        $storedQuery->setContextParameters($this->presenterContextParameters($presenter));
        return $storedQuery;
    }

    /**
     * @throws NoContestYearAvailable
     * @throws NoContestAvailable
     */
    public function createQuery(BasePresenter $presenter, QueryModel $patternQuery): StoredQuery
    {
        $storedQuery = StoredQuery::createFromQueryPattern($this->connection, $patternQuery);
        $storedQuery->setContextParameters($this->presenterContextParameters($presenter));
        return $storedQuery;
    }

    /**
     * @phpstan-param array<string,scalar> $parameters
     */
    public function createQueryFromQid(string $qid, array $parameters): StoredQuery
    {
        $patternQuery = $this->storedQueryService->findByQid($qid);
        if (!$patternQuery) {
            throw new InvalidArgumentException(sprintf(_('Unknown QID "%s".'), $qid));
        }
        $storedQuery = StoredQuery::createFromQueryPattern($this->connection, $patternQuery);
        $storedQuery->setContextParameters(
            $parameters,
            false
        ); // treat all parameters as implicit (better API for web service)
        return $storedQuery;
    }

    /**
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     * @phpstan-return array{
     *     contest_id:int,
     *     contest:int,
     *     year:int,
     *     series:int|null,
     *     ac_year:int,
     * }
     */
    private function presenterContextParameters(BasePresenter $presenter): array
    {
        return [
            self::PARAM_CONTEST_ID => $presenter->getSelectedContestYear()->contest_id,
            self::PARAM_CONTEST => $presenter->getSelectedContestYear()->contest_id,
            self::PARAM_YEAR => $presenter->getSelectedContestYear()->year,
            self::PARAM_AC_YEAR => $presenter->getSelectedContestYear()->ac_year,
            self::PARAM_SERIES => $presenter->getSelectedSeries(),
        ];
    }

    /**
     * @param StoredQuery $dataSource
     * @throws \DOMException
     * @throws BadTypeException
     */
    public function fillNode($dataSource, \DOMNode $node, \DOMDocument $doc, int $formatVersion): void
    {
        if (!$dataSource instanceof StoredQuery) {
            throw new BadTypeException(StoredQuery::class, $dataSource);
        }
        if ($formatVersion !== self::EXPORT_FORMAT_1 && $formatVersion !== self::EXPORT_FORMAT_2) {
            throw new InvalidArgumentException(sprintf(_('Export format %s not supported.'), $formatVersion));
        }
        // parameters
        $parametersNode = $doc->createElement('parameters');
        $node->appendChild($parametersNode);
        foreach ($dataSource->implicitParameterValues as $name => $value) {
            $parameterNode = $doc->createElement('parameter', (string)$value);
            $parameterNode->setAttribute('name', (string)$name);
            $parametersNode->appendChild($parameterNode);
        }

        // column definitions
        $columnDefinitionsNode = $doc->createElement('column-definitions');
        $node->appendChild($columnDefinitionsNode);
        foreach ($dataSource->getColumnNames() as $column) {
            $columnDefinitionNode = $doc->createElement('column-definition');
            $columnDefinitionNode->setAttribute('name', (string)$column);
            $columnDefinitionsNode->appendChild($columnDefinitionNode);
        }

        // data
        $dataNode = $doc->createElement('data');
        $node->appendChild($dataNode);
        foreach ($dataSource->getData() as $row) {
            $rowNode = $doc->createElement('row');
            $dataNode->appendChild($rowNode);
            foreach ($row as $colName => $value) {
                if (is_numeric($colName)) {
                    continue;
                }
                if ($formatVersion == self::EXPORT_FORMAT_1) {
                    $colNode = $doc->createElement('col');
                } else {
                    $colNode = $doc->createElement(Utils::xmlName($colName));
                }
                $textNode = $doc->createTextNode((string)$value);
                $colNode->appendChild($textNode);
                $rowNode->appendChild($colNode);
            }
        }
    }
}
