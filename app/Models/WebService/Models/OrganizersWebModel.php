<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\Models\OrganizerModel;
use FKSDB\Models\ORM\Services\ContestService;
use FKSDB\Models\WebService\XMLHelper;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

/**
 * @phpstan-extends WebModel<array{
 *     contest_id?:int,
 *     contestId:int,
 *     year?:int|null,
 * },array<mixed>>
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
        $organizers = $contest->getOrganizers();
        if (isset($args->year)) {
            $organizers->where('since<=?', $args->year)
                ->where('until IS NULL OR until >=?', $args->year);
        }

        $doc = new \DOMDocument();
        $rootNode = $doc->createElement('organizers');
        $doc->appendChild($rootNode);
        /** @var OrganizerModel $organizer */
        foreach ($organizers as $organizer) {
            $organizerNode = $doc->createElement('org');
            XMLHelper::fillArrayToNode([
                'name' => $organizer->person->getFullName(),
                'personId' => (string)$organizer->person_id,
                'academicDegreePrefix' => $organizer->person->getInfo()->academic_degree_prefix,
                'academicDegreeSuffix' => $organizer->person->getInfo()->academic_degree_suffix,
                'career' => $organizer->person->getInfo()->career,
                'contribution' => $organizer->contribution,
                'order' => (string)$organizer->order,
                'role' => $organizer->role,
                'since' => (string)$organizer->since,
                'until' => (string)$organizer->until,
                'texSignature' => $organizer->tex_signature,
                'domainAlias' => $organizer->domain_alias,
            ], $doc, $organizerNode);
            $rootNode->appendChild($organizerNode);
        }

        $doc->formatOutput = true;
        return new \SoapVar($doc->saveXML($rootNode), XSD_ANYXML);
    }

    public function getJsonResponse(array $params): array
    {
        $contest = $this->contestService->findByPrimary($params['contest_id'] ?? $params['contestId']);
        $organizers = $contest->getOrganizers();
        if (isset($params['year'])) {
            $organizers->where('since<=?', $params['year'])
                ->where('until IS NULL OR until >=?', $params['year']);
        }
        $items = [];
        /** @var OrganizerModel $organizer */
        foreach ($organizers as $organizer) {
            $items[] = array_merge($organizer->person->__toArray(), [
                'academicDegreePrefix' => $organizer->person->getInfo()->academic_degree_prefix,
                'academicDegreeSuffix' => $organizer->person->getInfo()->academic_degree_suffix,
                'career' => $organizer->person->getInfo()->career,
                'contribution' => $organizer->contribution,
                'order' => $organizer->order,
                'role' => $organizer->role,
                'since' => $organizer->since,
                'until' => $organizer->until,
                'texSignature' => $organizer->tex_signature,
                'domainAlias' => $organizer->domain_alias,
            ]);
        }
        return $items;
    }

    public function getExpectedParams(): Structure
    {
        return Expect::structure([
            'contestId' => Expect::scalar()->castTo('int'),
            'contest_id' => Expect::scalar()->castTo('int'),
            'year' => Expect::scalar()->castTo('int')->nullable(),
        ]);
    }

    protected function isAuthorized(array $params): bool
    {
        $contest = $this->contestService->findByPrimary($params['contest_id'] ?? $params['contestId']);
        return $this->contestAuthorizator->isAllowed($contest, 'api', $contest);
    }
}
