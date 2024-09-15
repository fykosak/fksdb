<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Contests;

use FKSDB\Models\Authorization\Resource\PseudoContestResource;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\OrganizerModel;
use FKSDB\Models\WebService\Models\SoapWebModel;
use FKSDB\Models\WebService\XMLHelper;
use FKSDB\Modules\CoreModule\RestApiPresenter;
use Nette\Schema\Expect;

/**
 * @phpstan-extends ContestWebModel<array{
 *     contest_id?:int,
 *     contestId:int,
 *     year?:int|null,
 * },array<mixed>>
 */
class OrganizersWebModel extends ContestWebModel implements SoapWebModel
{
    /**
     * @throws NotFoundException
     */
    protected function getJsonResponse(): array
    {
        $contest = $this->getContest();
        if (isset($this->params['year'])) {
            $contestYear = $contest->getContestYear($this->params['year']);
            if (!$contestYear) {
                throw new NotFoundException();
            }
            $organizers = $contestYear->getOrganizers();
        } else {
            $organizers = $contest->getOrganizers();
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

    protected function getExpectedParams(): array
    {
        return array_merge(
            parent::getExpectedParams(),
            [
                'year' => Expect::scalar()->castTo('int')->nullable(),
            ]
        );
    }

    /**
     * @throws NotFoundException
     */
    protected function isAuthorized(): bool
    {
        return $this->contestAuthorizator->isAllowed(
            new PseudoContestResource(RestApiPresenter::RESOURCE_ID, $this->getContest()),
            self::class,
            $this->getContest()
        );
    }

    /**
     * @throws \SoapFault
     * @throws \DOMException
     */
    public function getSOAPResponse(\stdClass $args): \SoapVar
    {
        if (!isset($args->contestId)) {
            throw new \SoapFault('Sender', 'Unknown contest.');
        }
        $contest = $this->contestService->findByPrimary($args->contestId);

        if (isset($args->year)) {
            $organizers = $contest->getContestYear($args->year)->getOrganizers();
        } else {
            $organizers = $contest->getOrganizers();
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
}
