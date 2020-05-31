<?php

namespace Exports;

use BasePresenter;
use DOMDocument;
use DOMNode;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQuery;
use FKSDB\ORM\Services\StoredQuery\ServiceStoredQuery;
use Nette\Application\BadRequestException;
use Nette\Database\Connection;
use Nette\Http\Response;
use Nette\InvalidArgumentException;
use Utils;
use WebService\IXMLNodeSerializer;
use FKSDB\CoreModule\ISeriesPresenter;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class StoredQueryFactory implements IXMLNodeSerializer {

    public const PARAM_CONTEST = 'contest';
    public const PARAM_YEAR = 'year';
    public const PARAM_SERIES = 'series';
    public const PARAM_AC_YEAR = 'ac_year';

    private Connection $connection;

    private ServiceStoredQuery $serviceStoredQuery;

    /**
     * StoredQueryFactory constructor.
     * @param Connection $connection
     * @param ServiceStoredQuery $serviceStoredQuery
     */
    public function __construct(Connection $connection, ServiceStoredQuery $serviceStoredQuery) {
        $this->connection = $connection;
        $this->serviceStoredQuery = $serviceStoredQuery;
    }

    /**
     * @param ISeriesPresenter $presenter
     * @param ModelStoredQuery $patternQuery
     * @return StoredQuery
     * @throws BadRequestException
     */
    public function createQuery(ISeriesPresenter $presenter, ModelStoredQuery $patternQuery): StoredQuery {
        $storedQuery = new StoredQuery($patternQuery, $this->connection);
        $this->presenterContextToQuery($presenter, $storedQuery);
        return $storedQuery;
    }

    /**
     * @param ISeriesPresenter $presenter
     * @param $sql
     * @param $parameters
     * @param array $queryData
     * @return StoredQuery
     * @throws BadRequestException
     */
    public function createQueryFromSQL(ISeriesPresenter $presenter, $sql, $parameters, $queryData = []): StoredQuery {
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
    public function createQueryFromQid($qid, $parameters): StoredQuery {
        $patternQuery = $this->serviceStoredQuery->findByQid($qid);
        if (!$patternQuery) {
            throw new InvalidArgumentException("Unknown QID '$qid'.");
        }
        $storedQuery = new StoredQuery($patternQuery, $this->connection);
        $storedQuery->setImplicitParameters($parameters, false); // treat all parameters as implicit (better API for web service)

        return $storedQuery;
    }

    /**
     * @param ISeriesPresenter $presenter
     * @param StoredQuery $storedQuery
     * @throws BadRequestException
     */
    private function presenterContextToQuery(ISeriesPresenter $presenter, StoredQuery $storedQuery) {
        // if (!$presenter instanceof Presenter) {
        //   throw new BadRequestException();
        // TODO forced added to IContest Presenter method flashMessage cause of tests
        // }
        $series = null;
        try {
            $series = $presenter->getSelectedSeries();
        } catch (BadRequestException $exception) {
            if ($exception->getCode() == Response::S500_INTERNAL_SERVER_ERROR) {
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
     * @param int $format
     * @return void
     * @throws BadRequestException
     */
    public function fillNode($dataSource, DOMNode $node, DOMDocument $doc, int $format) {
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
                if (is_numeric($colName)) {
                    continue;
                }
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
