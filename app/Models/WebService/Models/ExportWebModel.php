<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\Authorization\ContestAuthorizator;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Services\ContestService;
use FKSDB\Models\StoredQuery\StoredQuery;
use FKSDB\Models\StoredQuery\StoredQueryFactory;
use FKSDB\Models\WebService\XMLNodeSerializer;

class ExportWebModel extends WebModel
{
    private StoredQueryFactory $storedQueryFactory;
    private ContestAuthorizator $contestAuthorizator;
    private ContestService $contestService;

    public function inject(
        StoredQueryFactory $storedQueryFactory,
        ContestAuthorizator $contestAuthorizator,
        ContestService $contestService
    ): void {
        $this->storedQueryFactory = $storedQueryFactory;
        $this->contestAuthorizator = $contestAuthorizator;
        $this->contestService = $contestService;
    }

    /**
     * @throws \SoapFault
     * @throws \DOMException
     * @throws BadTypeException
     */
    public function getResponse(\stdClass $args): \SoapVar
    {
        // parse arguments
        if (!isset($args->qid)) {
            throw new \SoapFault('Sender', 'QId is not present');
        }
        $format = isset($args->{'format-version'})
            ? ((int)$args->{'format-version'})
            : XMLNodeSerializer::EXPORT_FORMAT_1;
        $parameters = [];

        // stupid PHP deserialization
        if (!is_array($args->parameter)) {
            $args->parameter = [$args->parameter];
        }
        foreach ($args->parameter as $parameter) {
            $parameters[$parameter->name] = $parameter->{'_'};
            if ($parameter->name == StoredQueryFactory::PARAM_CONTEST) {
                if (!isset($this->container->getParameters()['inverseContestMapping'][$parameters[$parameter->name]])) {
                    $msg = "Unknown contest '{$parameters[$parameter->name]}'.";
                    $this->log($msg);
                    throw new \SoapFault('Sender', $msg);
                }
                $parameters[$parameter->name] = $this->container->getParameters(
                )['inverseContestMapping'][$parameters[$parameter->name]];
            }
        }

        try {
            $storedQuery = $this->storedQueryFactory->createQueryFromQid($args->qid, $parameters);
        } catch (\InvalidArgumentException $exception) {
            throw new \SoapFault('Sender', $exception->getMessage(), (string)$exception);
        }

        // authorization
        if (!$this->isAuthorizedExport($storedQuery)) {
            $msg = 'Unauthorized';
            $this->log($msg);
            throw new \SoapFault('Sender', $msg);
        }

        $doc = new \DOMDocument();
        $exportNode = $doc->createElement('export');
        $exportNode->setAttribute('qid', (string)$args->qid);
        $doc->appendChild($exportNode);

        $this->storedQueryFactory->fillNode($storedQuery, $exportNode, $doc, $format);

        $doc->formatOutput = true;

        return new \SoapVar($doc->saveXML($exportNode), XSD_ANYXML);
    }

    private function isAuthorizedExport(StoredQuery $query): bool
    {
        if (!isset($query->implicitParameterValues[StoredQueryFactory::PARAM_CONTEST])) {
            return false;
        }
        return $this->contestAuthorizator->isAllowed(
            $query,
            'execute',
            $this->contestService->findByPrimary($query->implicitParameterValues[StoredQueryFactory::PARAM_CONTEST])
        );
    }
}
