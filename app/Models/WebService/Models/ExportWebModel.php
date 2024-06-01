<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Services\ContestService;
use FKSDB\Models\StoredQuery\StoredQuery;
use FKSDB\Models\StoredQuery\StoredQueryFactory;
use FKSDB\Models\WebService\XMLNodeSerializer;
use Nette\Schema\Elements\Structure;
use Nette\Security\User;
use Tracy\Debugger;

/**
 * @phpstan-extends WebModel<array<string,mixed>,array<string,mixed>>
 */
class ExportWebModel extends WebModel implements SoapWebModel
{
    private StoredQueryFactory $storedQueryFactory;
    private ContestService $contestService;
    private User $user;

    public function inject(
        StoredQueryFactory $storedQueryFactory,
        ContestService $contestService,
        User $user
    ): void {
        $this->storedQueryFactory = $storedQueryFactory;
        $this->contestService = $contestService;
        $this->user = $user;
    }

    /**
     * @throws \SoapFault
     * @throws \DOMException
     * @throws BadTypeException
     */
    public function getSOAPResponse(\stdClass $args): \SoapVar
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
            /** @phpstan-ignore-next-line */
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
            /** @phpstan-ignore-next-line */
            $this->contestService->findByPrimary($query->implicitParameterValues[StoredQueryFactory::PARAM_CONTEST])
        );
    }
    protected function log(string $msg): void
    {
        if (!$this->user->isLoggedIn()) {
            $message = 'unauthenticated@';
        } else {
            $message = $this->user->getIdentity()->__toString() . '@'; // @phpstan-ignore-line
        }
        $message .= $_SERVER['REMOTE_ADDR'] . "\t" . $msg;
        Debugger::log($message, 'soap');
    }

    protected function isAuthorized(): bool
    {
        return false;
    }

    protected function getExpectedParams(): array
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    protected function getJsonResponse(): array
    {
        throw new NotImplementedException();
    }
}
