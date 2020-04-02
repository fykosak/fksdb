<?php

namespace Exports;

use BasePresenter;
use DOMDocument;
use DOMNode;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQuery;
use FKSDB\ORM\Services\StoredQuery\ServiceStoredQuery;
use Nette\Application\BadRequestException;
use Nette\Database\Connection;
use Nette\InvalidArgumentException;
use OrgModule\SeriesPresenter;
use Utils;
use WebService\IXMLNodeSerializer;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class StoredQueryFactory implements IXMLNodeSerializer {

    const PARAM_CONTEST = 'contest';
    const PARAM_YEAR = 'year';
    const PARAM_SERIES = 'series';
    const PARAM_AC_YEAR = 'ac_year';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ServiceStoredQuery
     */
    private $serviceStoredQuery;

    /**
     * StoredQueryFactory constructor.
     * @param Connection $connection
     * @param ServiceStoredQuery $serviceStoredQuery
     */
    function __construct(Connection $connection, ServiceStoredQuery $serviceStoredQuery) {
        $this->connection = $connection;
        $this->serviceStoredQuery = $serviceStoredQuery;
    }

    /**
     * @param SeriesPresenter $presenter
     * @param ModelStoredQuery $patternQuery
     * @return StoredQuery
     * @throws BadRequestException
     */
    public function createQuery(SeriesPresenter $presenter, ModelStoredQuery $patternQuery): StoredQuery {
        $storedQuery = new StoredQuery($patternQuery, $this->connection);
        $this->presenterContextToQuery($presenter, $storedQuery);
        return $storedQuery;
    }

    /**
     * @param SeriesPresenter $presenter
     * @param $sql
     * @param $parameters
     * @param array $queryData
     * @return StoredQuery
     * @throws BadRequestException
     */
    public function createQueryFromSQL(SeriesPresenter $presenter, $sql, $parameters, $queryData = []): StoredQuery {
        /** @var ModelStoredQuery $patternQuery */
        $patternQuery = $this->serviceStoredQuery->createNew(array_merge([
            'sql' => $sql,
            'php_post_proc' => 0,
        ], $queryData));

        $patternQuery->setParameters($parameters);
        $storedQuery = new StoredQuery($patternQuery, $this->connection);
        $this->presenterContextToQuery($presenter, $storedQuery);

        return $storedQuery;
    }

    /**
     * @param $qid
     * @param $parameters
     * @return StoredQuery
     */
    public function createQueryFromQid($qid, $parameters) {
        $patternQuery = $this->serviceStoredQuery->findByQid($qid);
        if (!$patternQuery) {
            throw new InvalidArgumentException("Unknown QID '$qid'.");
        }
        $storedQuery = new StoredQuery($patternQuery, $this->connection);
        $storedQuery->setImplicitParameters($parameters, false); // treat all parameters as implicit (better API for web service)

        return $storedQuery;
    }

    /**
     * @param SeriesPresenter $presenter
     * @param StoredQuery $storedQuery
     * @throws BadRequestException
     */
    private function presenterContextToQuery(SeriesPresenter $presenter, StoredQuery $storedQuery) {
        $series = null;
        try {
            $series = $presenter->getSelectedSeries();
        } catch (BadRequestException $exception) {
            if ($exception->getCode() == 500) {
                $presenter->flashMessage(_('Kontext série pro dotazy není dostupný'), BasePresenter::FLASH_WARNING);
            } else {
                throw $exception;
            }
        }
        $storedQuery->setImplicitParameters([
            self::PARAM_CONTEST => $presenter->getSelectedContest()->contest_id,
            self::PARAM_YEAR => $presenter->getSelectedYear(),
            self::PARAM_AC_YEAR => $presenter->getSelectedAcademicYear(),
            self::PARAM_SERIES => $series,
        ]);
    }

    /**
     * @param $dataSource
     * @param DOMNode $node
     * @param DOMDocument $doc
     * @param $format
     * @return mixed|void
     * @throws BadRequestException
     */
    public function fillNode($dataSource, DOMNode $node, DOMDocument $doc, $format) {
        if (!$dataSource instanceof StoredQuery) {
            throw new InvalidArgumentException('Expected StoredQuery, got ' . get_class($dataSource) . '.');
        }
        if ($format !== self::EXPORT_FORMAT_1 && $format !== self::EXPORT_FORMAT_2) {
            throw new InvalidArgumentException(sprintf('Export format %s not supported.', $format));
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
                if ($format == self::EXPORT_FORMAT_1) {
                    $colNode = $doc->createElement('col');
                } elseif ($format == self::EXPORT_FORMAT_2) {
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

}
