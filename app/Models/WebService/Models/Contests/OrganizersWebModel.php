<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Contests;

use FKSDB\Models\ORM\Models\OrganizerModel;
use FKSDB\Models\ORM\Services\ContestService;
use FKSDB\Models\WebService\Models\WebModel;
use FKSDB\Models\WebService\XMLHelper;
use FKSDB\Modules\CoreModule\RestApiPresenter;
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

    protected function getJsonResponse(): array
    {
        $contest = $this->contestService->findByPrimary($this->params['contest_id'] ?? $this->params['contestId']);
        $organizers = $contest->getOrganizers();
        if (isset($this->params['year'])) {
            $organizers->where('since<=?', $this->params['year'])
                ->where('until IS NULL OR until >=?', $this->params['year']);
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

    protected function getExpectedParams(): Structure
    {
        return Expect::structure([
            'contestId' => Expect::scalar()->castTo('int'),
            'contest_id' => Expect::scalar()->castTo('int'),
            'year' => Expect::scalar()->castTo('int')->nullable(),
        ]);
    }

    protected function isAuthorized(): bool
    {
        $contest = $this->contestService->findByPrimary($this->params['contest_id'] ?? $this->params['contestId']);
        return $this->contestAuthorizator->isAllowed(RestApiPresenter::RESOURCE_ID, self::class, $contest);
    }
}
