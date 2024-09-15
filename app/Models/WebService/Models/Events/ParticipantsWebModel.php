<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Events;

use FKSDB\Models\Authorization\Resource\PseudoEventResource;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Modules\CoreModule\RestApiPresenter;
use Nette\Application\BadRequestException;

/**
 * @phpstan-extends EventWebModel<array{eventId:int,event_id:int},array<mixed>>
 */
class ParticipantsWebModel extends EventWebModel
{
    /**
     * @throws BadRequestException
     */
    protected function getJsonResponse(): array
    {
        $event = $this->getEvent();
        $data = [];
        /** @var EventParticipantModel $participant */
        foreach ($event->getParticipants() as $participant) {
            $history = $participant->getPersonHistory();
            $school = $history->school;
            $data[] = array_merge($participant->person->__toArray(), [
                'eventParticipantId' => $participant->event_participant_id,
                'status' => $participant->status->value,
                'lunchCount' => $participant->lunch_count ?? 0,
                'code' => $participant->createMachineCode(),
                'school' => $school ? $school->__toArray() : null,
                'studyYear' => $history ? $history->study_year_new->value : null,
            ]);
        }
        return $data;
    }

    /**
     * @throws NotFoundException
     */
    protected function isAuthorized(): bool
    {
        return $this->eventAuthorizator->isAllowed(
            new PseudoEventResource(RestApiPresenter::RESOURCE_ID, $this->getEvent()),
            self::class,
            $this->getEvent()
        );
    }
}
