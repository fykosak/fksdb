<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\Models\OrgModel;
use FKSDB\Models\ORM\Services\ContestService;
use FKSDB\Models\WebService\XMLHelper;
use Nette\SmartObject;

/**
 * @deprecated replaced by \FKSDB\Models\WebService\Models\OrganizersWebModel
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
        $organisers = $contest->getOrganisers();
        /** @var OrgModel $organiser */
        foreach ($organisers as $organiser) {
            $orgNode = $doc->createElement('org');
            XMLHelper::fillArrayToNode([
                'name' => $organiser->person->getFullName(),
                'texSignature' => $organiser->tex_signature,
                'domainAlias' => $organiser->domain_alias,
            ], $doc, $orgNode);
            $rootNode->appendChild($orgNode);
        }
        $doc->appendChild($rootNode);
        $doc->formatOutput = true;

        return new \SoapVar($doc->saveXML($rootNode), XSD_ANYXML);
    }
}
