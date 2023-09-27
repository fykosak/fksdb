<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Event;

use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\WebService\Models\WebModel;
use Nette\Application\BadRequestException;
use Nette\Http\IResponse;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

class ParticipantListWebModel extends WebModel
{
    private EventService $eventService;

    public function inject(EventService $eventService): void
    {
        $this->eventService = $eventService;
    }

    public function getExpectedParams(): Structure
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
        if (is_null($event)) {
            throw new BadRequestException('Unknown event.', IResponse::S404_NOT_FOUND);
        }
        $data = [];
        /** @var EventParticipantModel $participant */
        foreach ($event->getParticipants() as $participant) {
            $history = $participant->getPersonHistory();
            $data[] = [
                'name' => $participant->person->getFullName(),
                'personId' => $participant->person->person_id,
                'email' => $participant->person->getInfo()->email,
                'schoolId' => $history ? $history->school_id : null,
                'schoolName' => $history ? $history->school->name_abbrev : null,
                'studyYear' => $history ? $history->study_year_new->numeric() : null,
                'studyYearNew' => $history ? $history->study_year_new->value : null,
                'countryIso' => $history ? (
                ($school = $history->school) ? $school->address->country->alpha_2 : null
                ) : null,
            ];
        }
        return $data;
    }
}
