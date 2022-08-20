<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\DbNames;
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
     */
    public function getResponse(\stdClass $args): \SoapVar
    {
        if (!isset($args->contestId)) {
            throw new \SoapFault('Sender', 'Unknown contest.');
        }
        $contest = $this->contestService->findByPrimary($args->contestId);

        $doc = new \DOMDocument();

        $rootNode = $doc->createElement('signatures');
        $organisers = $contest->related(DbNames::TAB_ORG);
        /** @var OrgModel $org */
        foreach ($organisers as $org) {
            $orgNode = $doc->createElement('org');
            XMLHelper::fillArrayToNode([
                'name' => $org->person->getFullName(),
                'texSignature' => $org->tex_signature,
                'domainAlias' => $org->domain_alias,
            ], $doc, $orgNode);
            $rootNode->appendChild($orgNode);
        }
        $doc->appendChild($rootNode);
        $doc->formatOutput = true;

        return new \SoapVar($doc->saveXML($rootNode), XSD_ANYXML);
    }
}
