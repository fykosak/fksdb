<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\OrganizerModel;
use FKSDB\Models\ORM\Services\ContestService;
use FKSDB\Models\WebService\XMLHelper;
use Nette\Schema\Elements\Structure;
use Nette\SmartObject;

/**
 * @deprecated replaced by \FKSDB\Models\WebService\Models\OrganizersWebModel
 * @phpstan-extends WebModel<array<string,mixed>,array<string,mixed>>
 */
class SignaturesWebModel extends WebModel
{
    use SmartObject;

    private ContestService $contestService;

    public function inject(ContestService $contestService): void
    {
        $this->contestService = $contestService;
    }

    /**
     * @throws \SoapFault
     * @throws \DOMException
     */
    public function getResponse(\stdClass $args): \SoapVar
    {
        if (!isset($args->contestId)) {
            throw new \SoapFault('Sender', 'Unknown contest.');
        }
        $contest = $this->contestService->findByPrimary($args->contestId);

        $doc = new \DOMDocument();

        $rootNode = $doc->createElement('signatures');
        $organizers = $contest->getOrganizers();
        /** @var OrganizerModel $organizer */
        foreach ($organizers as $organizer) {
            $organizerNode = $doc->createElement('org');
            XMLHelper::fillArrayToNode([
                'name' => $organizer->person->getFullName(),
                'texSignature' => $organizer->tex_signature,
                'domainAlias' => $organizer->domain_alias,
            ], $doc, $organizerNode);
            $rootNode->appendChild($organizerNode);
        }
        $doc->appendChild($rootNode);
        $doc->formatOutput = true;

        return new \SoapVar($doc->saveXML($rootNode), XSD_ANYXML);
    }

    protected function isAuthorized(): bool
    {
        return false;
    }

    /**
     * @throws GoneException
     */
    protected function getExpectedParams(): Structure
    {
        throw new GoneException();
    }

    /**
     * @throws GoneException
     */
    protected function getJsonResponse(): array
    {
        throw new GoneException();
    }
}
