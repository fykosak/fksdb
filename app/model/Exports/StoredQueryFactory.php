<?php

namespace Exports;

use BasePresenter;
use DOMDocument;
use DOMNode;

use ISeriesPresenter;
use ModelStoredQuery;
use Nette\Application\BadRequestException;
use Nette\Database\Connection;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
use ServiceStoredQuery;
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
     * @var ISeriesPresenter
     */
    private $presenter;

    function __construct(Connection $connection, ServiceStoredQuery $serviceStoredQuery) {
        $this->connection = $connection;
        $this->serviceStoredQuery = $serviceStoredQuery;
    }

    public function getPresenter() {
        return $this->presenter;
    }

    public function setPresenter(ISeriesPresenter $presenter) {
        $this->presenter = $presenter;
    }

    public function createQuery(ModelStoredQuery $patternQuery) {
        $storedQuery = new StoredQuery($patternQuery, $this->connection);
        $this->presenterContextToQuery($storedQuery);

        return $storedQuery;
    }

    public function createQueryFromSQL($sql, $parameters, $queryData = []) {
        $patternQuery = $this->serviceStoredQuery->createNew(array_merge(array(
            'sql' => $sql,
            'php_post_proc' => 0,
                        ), $queryData));

        $patternQuery->setParameters($parameters);
        $storedQuery = new StoredQuery($patternQuery, $this->connection);
        $this->presenterContextToQuery($storedQuery);

        return $storedQuery;
    }

    public function createQueryFromQid($qid, $parameters) {
        $patternQuery = $this->serviceStoredQuery->findByQid($qid);
        if (!$patternQuery) {
            throw new InvalidArgumentException("Unknown QID '$qid'.");
        }
        $storedQuery = new StoredQuery($patternQuery, $this->connection);
        $storedQuery->setImplicitParameters($parameters, false); // treat all parameters as implicit (better API for web service)

        return $storedQuery;
    }

    private function presenterContextToQuery(StoredQuery $storedQuery) {
        if (!$this->getPresenter()) {
            throw new InvalidStateException("Must provide provider of context for implicit parameters.");
        }

        $presenter = $this->getPresenter();
        $series = null;
        try {
            $series = $presenter->getSelectedSeries();
        } catch (BadRequestException $e) {
            if ($e->getCode() == 500) {
                $presenter->flashMessage(_('Kontext série pro dotazy není dostupný'), BasePresenter::FLASH_WARNING);
            } else {
                throw $e;
            }
        }
        $storedQuery->setImplicitParameters(array(
            self::PARAM_CONTEST => $presenter->getSelectedContest()->contest_id,
            self::PARAM_YEAR => $presenter->getSelectedYear(),
            self::PARAM_AC_YEAR => $presenter->getSelectedAcademicYear(),
            self::PARAM_SERIES => $series,
        ));
    }

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
                }
                $textNode = $doc->createTextNode($value);
                $colNode->appendChild($textNode);
                $rowNode->appendChild($colNode);
            }
        }
    }

}
