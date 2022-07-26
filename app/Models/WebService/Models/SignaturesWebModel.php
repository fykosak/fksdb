<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelOrg;
use FKSDB\Models\ORM\Services\ServiceContest;
use FKSDB\Models\WebService\XMLHelper;
use Nette\SmartObject;

/**
 * @deprecated replaced by \FKSDB\Models\WebService\Models\OrganizersWebModel
 */
class SignaturesWebModel extends WebModel
{
    use SmartObject;

    private ServiceContest $serviceContest;

    public function inject(ServiceContest $serviceContest): void
    {
        $this->serviceContest = $serviceContest;
    }

    /**
     * @throws \SoapFault
     */
    public function getResponse(\stdClass $args): \SoapVar
    {
        if (!isset($args->contestId)) {
            throw new \SoapFault('Sender', 'Unknown contest.');
        }
        $contest = $this->serviceContest->findByPrimary($args->contestId);

        $doc = new \DOMDocument();

        $rootNode = $doc->createElement('signatures');
        $organisers = $contest->related(DbNames::TAB_ORG);
        foreach ($organisers as $row) {
            $org = ModelOrg::createFromActiveRow($row, $contest->mapper);
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
