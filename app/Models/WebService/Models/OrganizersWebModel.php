<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelOrg;
use FKSDB\Models\ORM\Services\ServiceContest;
use FKSDB\Models\ORM\Services\ServiceOrg;
use FKSDB\Models\WebService\XMLHelper;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

class OrganizersWebModel extends WebModel
{

    private ServiceOrg $serviceOrg;
    private ServiceContest $serviceContest;

    public function inject(ServiceOrg $serviceOrg, ServiceContest $serviceContest): void
    {
        $this->serviceOrg = $serviceOrg;
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
                'name' => $org->person->getFullName(),
                'personId' => $org->person_id,
                'academicDegreePrefix' => $org->person->getInfo()->academic_degree_prefix,
                'academicDegreeSuffix' => $org->person->getInfo()->academic_degree_suffix,
                'career' => $org->person->getInfo()->career,
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

    public function getJsonResponse(array $params): array
    {
        $contest = $this->serviceContest->findByPrimary($params['contestId']);
        $organisers = $contest->related(DbNames::TAB_ORG);
        if (isset($params['year'])) {
            $organisers->where('since<=?', $params['year'])
                ->where('until IS NULL OR until >=?', $params['year']);
        }
        $items = [];
        foreach ($organisers as $row) {
            $org = ModelOrg::createFromActiveRow($row, $this->serviceContest->mapper);
            $items[] = [
                'name' => $org->person->getFullName(),
                'personId' => $org->person_id,
                'academicDegreePrefix' => $org->person->getInfo()->academic_degree_prefix,
                'academicDegreeSuffix' => $org->person->getInfo()->academic_degree_suffix,
                'career' => $org->person->getInfo()->career,
                'contribution' => $org->contribution,
                'order' => $org->order,
                'role' => $org->role,
                'since' => $org->since,
                'until' => $org->until,
                'texSignature' => $org->tex_signature,
                'domainAlias' => $org->domain_alias,
            ];
        }
        return $items;
    }

    public function getExpectedParams(): Structure
    {
        return Expect::structure([
            'contest_id' => Expect::scalar()->castTo('int')->required(),
            'year' => Expect::scalar()->castTo('int')->nullable(),
        ]);
    }
}
