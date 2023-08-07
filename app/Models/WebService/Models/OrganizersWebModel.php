<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\Models\OrgModel;
use FKSDB\Models\ORM\Services\ContestService;
use FKSDB\Models\WebService\XMLHelper;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

/**
 * @phpstan-extends WebModel<array<string,mixed>,array<string,mixed>>
 */
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
        /** @var OrgModel $org */
        foreach ($organisers as $org) {
            $orgNode = $doc->createElement('org');
            XMLHelper::fillArrayToNode([
                'name' => $org->person->getFullName(),
                'personId' => (string)$org->person_id,
                'academicDegreePrefix' => $org->person->getInfo()->academic_degree_prefix,
                'academicDegreeSuffix' => $org->person->getInfo()->academic_degree_suffix,
                'career' => $org->person->getInfo()->career,
                'contribution' => $org->contribution,
                'order' => (string)$org->order,
                'role' => $org->role,
                'since' => (string)$org->since,
                'until' => (string)$org->until,
                'texSignature' => $org->tex_signature,
                'domainAlias' => $org->domain_alias,
            ], $doc, $orgNode);
            $rootNode->appendChild($orgNode);
        }

        $doc->formatOutput = true;
        return new \SoapVar($doc->saveXML($rootNode), XSD_ANYXML);
    }

    /**
     * @phpstan-param array{
     *     contest_id:int,
     *     year?:int|null,
     * } $params
     */
    public function getJsonResponse(array $params): array
    {
        $contest = $this->contestService->findByPrimary($params['contest_id']);
        $organisers = $contest->getOrganisers();
        if (isset($params['year'])) {
            $organisers->where('since<=?', $params['year'])
                ->where('until IS NULL OR until >=?', $params['year']);
        }
        $items = [];
        /** @var OrgModel $org */
        foreach ($organisers as $org) {
            $items[] = [
                'name' => $org->person->getFullName(),
                'personId' => $org->person_id,
                'email' => $org->person->getInfo()->email,
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
