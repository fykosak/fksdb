<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\Authorization\ContestAuthorizator;
use FKSDB\Models\ORM\Services\ServiceContest;
use FKSDB\Models\StoredQuery\StoredQuery;
use FKSDB\Models\StoredQuery\StoredQueryFactory;
use FKSDB\Models\WebService\XMLNodeSerializer;
use Nette\Application\BadRequestException;

class ExportWebModel extends WebModel
{

    private StoredQueryFactory $storedQueryFactory;
    private ContestAuthorizator $contestAuthorizator;
    private ServiceContest $serviceContest;

    public function inject(
        StoredQueryFactory $storedQueryFactory,
        ContestAuthorizator $contestAuthorizator,
        ServiceContest $serviceContest
    ): void {
        $this->storedQueryFactory = $storedQueryFactory;
        $this->contestAuthorizator = $contestAuthorizator;
        $this->serviceContest = $serviceContest;
    }

    /**
     * @throws BadRequestException
     * @throws \SoapFault
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
            throw new \SoapFault('Sender', $exception->getMessage(), $exception);
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
        $implicitParameters = $query->getImplicitParameters();
        if (!isset($implicitParameters[StoredQueryFactory::PARAM_CONTEST])) {
            return false;
        }
        return $this->contestAuthorizator->isAllowedForLogin(
            $this->authenticatedLogin,
            $query,
            'execute',
            $this->serviceContest->findByPrimary($implicitParameters[StoredQueryFactory::PARAM_CONTEST])
        );
    }
}
