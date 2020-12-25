<?php

namespace FKSDB\Models\StoredQuery;

use DOMDocument;
use DOMNode;
use FKSDB\Models\ORM\Models\StoredQuery\ModelStoredQuery;
use FKSDB\Models\ORM\Models\StoredQuery\ModelStoredQueryParameter;
use FKSDB\Models\ORM\Services\StoredQuery\ServiceStoredQuery;
use FKSDB\Modules\OrgModule\BasePresenter;
use Nette\Application\BadRequestException;
use Nette\Database\Connection;
use Nette\InvalidArgumentException;
use FKSDB\Models\Utils\Utils;
use FKSDB\Models\WebService\IXMLNodeSerializer;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class StoredQueryFactory implements IXMLNodeSerializer {

    public const PARAM_CONTEST_ID = 'contest_id';
    public const PARAM_CONTEST = 'contest';
    public const PARAM_YEAR = 'year';
    public const PARAM_SERIES = 'series';
    public const PARAM_AC_YEAR = 'ac_year';
    private Connection $connection;
    private ServiceStoredQuery $serviceStoredQuery;

    public function __construct(Connection $connection, ServiceStoredQuery $serviceStoredQuery) {
        $this->connection = $connection;
        $this->serviceStoredQuery = $serviceStoredQuery;
    }

    /**
     * @param BasePresenter $presenter
     * @param string $sql
     * @param ModelStoredQueryParameter[]|StoredQueryParameter[] $parameters
     * @param string|null $postProcessingClass
     * @return StoredQuery
     * @throws BadRequestException
     */
    public function createQueryFromSQL(BasePresenter $presenter, string $sql, array $parameters, ?string $postProcessingClass = null): StoredQuery {
        $storedQuery = StoredQuery::createWithoutQueryPattern($this->connection, $sql, $parameters, $postProcessingClass);
        $storedQuery->setContextParameters($this->presenterContextParameters($presenter));
        return $storedQuery;
    }

    /**
     * @param BasePresenter $presenter
     * @param ModelStoredQuery $patternQuery
     * @return StoredQuery
     * @throws BadRequestException
     */
    public function createQuery(BasePresenter $presenter, ModelStoredQuery $patternQuery): StoredQuery {
        $storedQuery = StoredQuery::createFromQueryPattern($this->connection, $patternQuery);
        $storedQuery->setContextParameters($this->presenterContextParameters($presenter));
        return $storedQuery;
    }

    public function createQueryFromQid(string $qid, array $parameters): StoredQuery {
        $patternQuery = $this->serviceStoredQuery->findByQid($qid);
        if (!$patternQuery) {
            throw new InvalidArgumentException("Unknown QID '$qid'.");
        }
        $storedQuery = StoredQuery::createFromQueryPattern($this->connection, $patternQuery);
        $storedQuery->setContextParameters($parameters, false); // treat all parameters as implicit (better API for web service)
        return $storedQuery;
    }

    private function presenterContextParameters(BasePresenter $presenter): array {
        return [
            self::PARAM_CONTEST_ID => $presenter->getSelectedContest()->contest_id,
            self::PARAM_CONTEST => $presenter->getSelectedContest()->contest_id,
            self::PARAM_YEAR => $presenter->getSelectedYear(),
            self::PARAM_AC_YEAR => $presenter->getSelectedAcademicYear(),
            self::PARAM_SERIES => $presenter->getSelectedSeries(),
        ];
    }

    /**
     * @param StoredQuery $dataSource
     * @param DOMNode $node
     * @param DOMDocument $doc
     * @param int $formatVersion
     * @return void
     * @throws BadRequestException
     */
    public function fillNode($dataSource, DOMNode $node, DOMDocument $doc, int $formatVersion): void {
        if (!$dataSource instanceof StoredQuery) {
            throw new InvalidArgumentException('Expected StoredQuery, got ' . get_class($dataSource) . '.');
        }
        if ($formatVersion !== self::EXPORT_FORMAT_1 && $formatVersion !== self::EXPORT_FORMAT_2) {
            throw new InvalidArgumentException(sprintf('Export format %s not supported.', $formatVersion));
        }
        // parameters
        $parametersNode = $doc->createElement('parameters');
        $node->appendChild($parametersNode);
        foreach ($dataSource->getImplicitParameters() as $name => $value) {
            $parameterNode = $doc->createElement('parameter', $value);
            $parameterNode->setAttribute('name', $name);
            $parametersNode->appendChild($parameterNode);
        }

        // column definitions
        $columnDefinitionsNode = $doc->createElement('column-definitions');
        $node->appendChild($columnDefinitionsNode);
        foreach ($dataSource->getColumnNames() as $column) {
            $columnDefinitionNode = $doc->createElement('column-definition');
            $columnDefinitionNode->setAttribute('name', $column);
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
                } elseif ($formatVersion == self::EXPORT_FORMAT_2) {
                    $colNode = $doc->createElement(Utils::xmlName($colName));
                } else {
                    throw new BadRequestException(_('Unsupported format'));
                }
                $textNode = $doc->createTextNode($value);
                $colNode->appendChild($textNode);
                $rowNode->appendChild($colNode);
            }
        }
    }

    public function createParameterFromModel(ModelStoredQueryParameter $model): StoredQueryParameter {
        return new StoredQueryParameter($model->name, $model->getDefaultValue(), $model->getPDOType(), $model->description);
    }
}
