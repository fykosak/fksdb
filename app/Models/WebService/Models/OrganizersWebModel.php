<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\ModelOrg;
use FKSDB\Models\ORM\Services\ServiceOrg;
use FKSDB\Models\WebService\XMLHelper;

class OrganizersWebModel extends WebModel
{

    private ServiceOrg $serviceOrg;

    public function inject(ServiceOrg $serviceOrg): void
    {
        $this->serviceOrg = $serviceOrg;
    }

    /**
     * @throws \SoapFault
     */
    public function getResponse(\stdClass $args): \SoapVar
    {
        if (!isset($args->contestId)) {
            throw new \SoapFault('Sender', 'Unknown contest.');
        }
        $organisers = $this->serviceOrg->getTable()->where('contest_id', $args->contestId);
        if (isset($args->year)) {
            $organisers->where('since<=?', $args->year)->where('until IS NULL OR until >=?', $args->year);
        }

        $doc = new \DOMDocument();
        $rootNode = $doc->createElement('organizers');
        $doc->appendChild($rootNode);
        /** @var ModelOrg $org */
        foreach ($organisers as $org) {
            $orgNode = $doc->createElement('org');
            XMLHelper::fillArrayToNode([
                'name' => $org->getPerson()->getFullName(),
                'personId' => $org->person_id,
                'academicDegreePrefix' => $org->getPerson()->getInfo()->academic_degree_prefix,
                'academicDegreeSuffix' => $org->getPerson()->getInfo()->academic_degree_suffix,
                'career' => $org->getPerson()->getInfo()->career,
                'contribution' => $org->contribution,
                'order' => $org->order,
                'role' => $org->role,
                'since' => $org->since,
                'until' => $org->until,
                'texSignature' => $org->tex_signature,
                'domainAlias' => $org->domain_alias,
            ], $doc, $orgNode);
            $rootNode->appendChild($orgNode);
        }

        $doc->formatOutput = true;
        return new \SoapVar($doc->saveXML($rootNode), XSD_ANYXML);
    }

    /**
     * @throws GoneException
     */
    public function getJsonResponse(array $params): array
    {
        throw new GoneException();
    }
}
