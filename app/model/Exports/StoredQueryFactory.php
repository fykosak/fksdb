<?php

namespace Exports;

use FKSDB\Modules\Core\BasePresenter;
use DOMDocument;
use DOMNode;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQuery;
use FKSDB\ORM\Services\StoredQuery\ServiceStoredQuery;
use Nette\Application\BadRequestException;
use Nette\Database\Connection;
use Nette\Http\Response;
use Nette\InvalidArgumentException;
use FKSDB\Utils\Utils;
use WebService\IXMLNodeSerializer;
use FKSDB\Modules\Core\PresenterTraits\ISeriesPresenter;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class StoredQueryFactory implements IXMLNodeSerializer {

    const PARAM_CONTEST_ID = 'contest_id';
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
        $storedQuery = new StoredQuery($this->connection);
        $storedQuery->setQueryPattern($patternQuery);
        $storedQuery->setContextParameters($this->presenterContextParameters($presenter));
        return $storedQuery;
    }

    /**
     * @param ISeriesPresenter $presenter
     * @param string $sql
     * @param array $parameters
     * @param string $postProcessingClass
     * @return StoredQuery
     * @throws BadRequestException
     */
    public function createQueryFromSQL(ISeriesPresenter $presenter, string $sql, array $parameters, string $postProcessingClass = null): StoredQuery {
        $storedQuery = new StoredQuery($this->connection);
        $storedQuery->setSQL($sql);
        $storedQuery->setQueryParameters($parameters);
        $storedQuery->setContextParameters($this->presenterContextParameters($presenter));
        if ($postProcessingClass) {
            $storedQuery->setPostProcessing($postProcessingClass);
        }
        return $storedQuery;
    }

    public function createQueryFromQid(string $qid, array $parameters): StoredQuery {
        $patternQuery = $this->serviceStoredQuery->findByQid($qid);
        if (!$patternQuery) {
            throw new InvalidArgumentException("Unknown QID '$qid'.");
        }
        $storedQuery = new StoredQuery($this->connection);
        $storedQuery->setQueryPattern($patternQuery);
        $storedQuery->setContextParameters($parameters, false); // treat all parameters as implicit (better API for web service)
        return $storedQuery;
    }

    /**
     * @param ISeriesPresenter $presenter
     * @return array
     * @throws BadRequestException
     */
    private function presenterContextParameters(ISeriesPresenter $presenter): array {
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
        return [
            self::PARAM_CONTEST_ID => $presenter->getSelectedContest()->contest_id,
            self::PARAM_CONTEST => $presenter->getSelectedContest()->contest_id,
            self::PARAM_YEAR => $presenter->getSelectedYear(),
            self::PARAM_AC_YEAR => $presenter->getSelectedAcademicYear(),
            self::PARAM_SERIES => $series,
        ];
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
