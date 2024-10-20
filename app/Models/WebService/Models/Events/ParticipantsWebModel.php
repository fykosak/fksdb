<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Events;

use FKSDB\Models\Authorization\Resource\EventResourceHolder;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
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

            $participantSchedule = [];
            /** @var PersonScheduleModel $personSchedule */
            foreach ($participant->getSchedule() as $personSchedule) {
                $participantSchedule[] = [
                    'personScheduleId' => $personSchedule->person_schedule_id,
                    'scheduleItemId' => $personSchedule->schedule_item_id,
                    'paymentDeadline' => $personSchedule->payment_deadline,
                    'state' => $personSchedule->state->value,
                ];
            }

            $data[] = array_merge($participant->person->__toArray(), [
                'eventParticipantId' => $participant->event_participant_id,
                'status' => $participant->status->value,
                'lunchCount' => $participant->lunch_count ?? 0,
                'code' => $participant->createMachineCode(),
                'school' => $school ? $school->__toArray() : null,
                'studyYear' => $history ? $history->study_year_new->value : null,
                'schedule' => $participantSchedule
            ]);
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
