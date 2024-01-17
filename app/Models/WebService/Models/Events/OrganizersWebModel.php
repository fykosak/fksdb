<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Events;

use FKSDB\Models\ORM\Models\EventOrganizerModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\WebService\Models\WebModel;
use Nette\Application\BadRequestException;
use Nette\Http\IResponse;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

/**
 * @phpstan-import-type TSimplePersonArray from PersonModel
 * @phpstan-extends WebModel<array{eventId:int},(array{person:TSimplePersonArray,organizerId:int,note:string|null})[]>
 */
class OrganizersWebModel extends WebModel
{
    private EventService $eventService;

    public function inject(EventService $eventService): void
    {
        $this->eventService = $eventService;
    }

    protected function getExpectedParams(): Structure
    {
        return Expect::structure([
            'eventId' => Expect::scalar()->castTo('int')->required(),
        ]);
    }

    /**
     * @throws BadRequestException
     */
    protected function getJsonResponse(array $params): array
    {
        $event = $this->eventService->findByPrimary($params['eventId']);
        if (!$event) {
            throw new BadRequestException('Unknown event.', IResponse::S404_NOT_FOUND);
        }
        $data = [];
        /** @var EventOrganizerModel $organizer */
        foreach ($event->getEventOrganizers() as $organizer) {
            $data[] = [
                'person' => $organizer->person->__toArray(),
                'organizerId' => $organizer->e_org_id,
                'note' => $organizer->note,
            ];
        }
        return $data;
    }

    protected function isAuthorized(array $params): bool
    {
        $event = $this->eventService->findByPrimary($params['eventId']);
        if (!$event) {
            return false;
        }
        return $this->eventAuthorizator->isAllowed($event, 'api', $event);
    }
}
