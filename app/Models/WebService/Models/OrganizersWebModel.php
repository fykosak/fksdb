<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\OrgModel;
use FKSDB\Models\ORM\Services\ContestService;
use FKSDB\Models\ORM\Services\OrgService;
use FKSDB\Models\WebService\XMLHelper;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

class OrganizersWebModel extends WebModel
{

    private OrgService $orgService;
    private ContestService $contestService;

    public function inject(OrgService $orgService, ContestService $contestService): void
    {
        $this->orgService = $orgService;
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
        $organisers = $this->orgService->getTable()->where('contest_id', $args->contestId);
        if (isset($args->year)) {
            $organisers->where('since<=?', $args->year)->where('until IS NULL OR until >=?', $args->year);
        }

        $doc = new \DOMDocument();
        $rootNode = $doc->createElement('organizers');
        $doc->appendChild($rootNode);
        /** @var OrgModel $org */
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
        $contest = $this->contestService->findByPrimary($params['contestId']);
        $organisers = $contest->related(DbNames::TAB_ORG);
        if (isset($params['year'])) {
            $organisers->where('since<=?', $params['year'])
                ->where('until IS NULL OR until >=?', $params['year']);
        }
        $items = [];
        foreach ($organisers as $row) {
            $org = OrgModel::createFromActiveRow($row);
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
