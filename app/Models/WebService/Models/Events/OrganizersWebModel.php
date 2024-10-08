<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Events;

use FKSDB\Models\Authorization\Resource\EventResourceHolder;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\EventOrganizerModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Modules\CoreModule\RestApiPresenter;
use Nette\Application\BadRequestException;

/**
 * @phpstan-import-type TSimplePersonArray from PersonModel
 * @phpstan-extends EventWebModel<array{eventId:int},(array{person:TSimplePersonArray,organizerId:int,note:string|null})[]>
 */
class OrganizersWebModel extends EventWebModel
{
    /**
     * @throws BadRequestException
     */
    protected function getJsonResponse(): array
    {
        $data = [];
        /** @var EventOrganizerModel $organizer */
        foreach ($this->getEvent()->getEventOrganizers() as $organizer) {
            $data[] = [
                'person' => $organizer->person->__toArray(),
                'organizerId' => $organizer->e_org_id,
                'note' => $organizer->note,
            ];
        }
        return $data;
    }

    /**
     * @throws NotFoundException
     */
    protected function isAuthorized(): bool
    {
        return $this->authorizator->isAllowedEvent(
            EventResourceHolder::fromResourceId(RestApiPresenter::RESOURCE_ID, $this->getEvent()),
            self::class,
            $this->getEvent()
        );
    }
}
