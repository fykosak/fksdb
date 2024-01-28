<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Events;

use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\WebService\Models\WebModel;
use FKSDB\Modules\CoreModule\RestApiPresenter;
use Nette\Application\BadRequestException;
use Nette\Http\IResponse;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

/**
 * @phpstan-extends WebModel<array{eventId:int,event_id:int},array<mixed>>
 */
class ParticipantsWebModel extends WebModel
{
    private EventService $eventService;

    public function inject(EventService $eventService): void
    {
        $this->eventService = $eventService;
    }

    protected function getExpectedParams(): Structure
    {
        return Expect::structure([
            'eventId' => Expect::scalar()->castTo('int'),
            'event_id' => Expect::scalar()->castTo('int'),
        ]);
    }

    /**
     * @throws BadRequestException
     */
    protected function getJsonResponse(): array
    {
        $event = $this->eventService->findByPrimary(
            $this->params['eventId'] ?? $this->params['event_id']
        );
        if (!$event) {
            throw new BadRequestException('Unknown event.', IResponse::S404_NOT_FOUND);
        }
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

    protected function isAuthorized(): bool
    {
        $event = $this->eventService->findByPrimary($this->params['eventId'] ?? $this->params['event_id']);
        if (!$event) {
            return false;
        }
        return $this->eventAuthorizator->isAllowed(RestApiPresenter::RESOURCE_ID, self::class, $event);
    }
}
