<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelOrg;
use FKSDB\Models\ORM\Services\ServiceContest;
use FKSDB\Models\WebService\XMLHelper;

class SignaturesWebModel extends WebModel
{
    private ServiceContest $serviceContest;

    public function inject(ServiceContest $serviceContest): void
    {
        $this->serviceContest = $serviceContest;
    }

    /**
     * @param \stdClass $args
     * @return \SoapVar
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
        foreach ($contest->related(DbNames::TAB_ORG) as $row) {
            $org = ModelOrg::createFromActiveRow($row);
            $orgNode = $doc->createElement('org');
            XMLHelper::fillArrayToNode([
                'name' => $org->getPerson()->getFullName(),
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
