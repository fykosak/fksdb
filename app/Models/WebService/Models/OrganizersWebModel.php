<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\Models\OrgModel;
use FKSDB\Models\ORM\Services\ContestService;
use FKSDB\Models\WebService\XMLHelper;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

class OrganizersWebModel extends WebModel
{
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
        $organisers = $contest->getOrganisers();
        if (isset($args->year)) {
            $organisers->where('since<=?', $args->year)
                ->where('until IS NULL OR until >=?', $args->year);
        }

        $doc = new \DOMDocument();
        $rootNode = $doc->createElement('organizers');
        $doc->appendChild($rootNode);
        /** @var OrgModel $organiser */
        foreach ($organisers as $organiser) {
            $orgNode = $doc->createElement('org');
            XMLHelper::fillArrayToNode([
                'name' => $organiser->person->getFullName(),
                'personId' => $organiser->person_id,
                'academicDegreePrefix' => $organiser->person->getInfo()->academic_degree_prefix,
                'academicDegreeSuffix' => $organiser->person->getInfo()->academic_degree_suffix,
                'career' => $organiser->person->getInfo()->career,
                'contribution' => $organiser->contribution,
                'order' => $organiser->order,
                'role' => $organiser->role,
                'since' => $organiser->since,
                'until' => $organiser->until,
                'texSignature' => $organiser->tex_signature,
                'domainAlias' => $organiser->domain_alias,
            ], $doc, $orgNode);
            $rootNode->appendChild($orgNode);
        }

        $doc->formatOutput = true;
        return new \SoapVar($doc->saveXML($rootNode), XSD_ANYXML);
    }

    public function getJsonResponse(array $params): array
    {
        $contest = $this->contestService->findByPrimary($params['contestId']);
        $organisers = $contest->getOrganisers();
        if (isset($params['year'])) {
            $organisers->where('since<=?', $params['year'])
                ->where('until IS NULL OR until >=?', $params['year']);
        }
        $items = [];
        /** @var OrgModel $organiser */
        foreach ($organisers as $organiser) {
            $items[] = [
                'name' => $organiser->person->getFullName(),
                'personId' => $organiser->person_id,
                'academicDegreePrefix' => $organiser->person->getInfo()->academic_degree_prefix,
                'academicDegreeSuffix' => $organiser->person->getInfo()->academic_degree_suffix,
                'career' => $organiser->person->getInfo()->career,
                'contribution' => $organiser->contribution,
                'order' => $organiser->order,
                'role' => $organiser->role,
                'since' => $organiser->since,
                'until' => $organiser->until,
                'texSignature' => $organiser->tex_signature,
                'domainAlias' => $organiser->domain_alias,
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
