<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Contests;

use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\OrganizerModel;
use FKSDB\Modules\CoreModule\RestApiPresenter;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

/**
 * @phpstan-extends ContestWebModel<array{
 *     contest_id?:int,
 *     contestId:int,
 *     year?:int|null,
 * },array<mixed>>
 */
class OrganizersWebModel extends ContestWebModel
{
    /**
     * @throws NotFoundException
     */
    protected function getJsonResponse(): array
    {
        $contest = $this->getContest();
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
            'year' => Expect::scalar()->castTo('int')->nullable(),
        ]);
    }

    /**
     * @throws NotFoundException
     */
    protected function isAuthorized(): bool
    {
        return $this->contestAuthorizator->isAllowed(RestApiPresenter::RESOURCE_ID, self::class, $this->getContest());
    }
}
