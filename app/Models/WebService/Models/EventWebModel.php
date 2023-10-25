<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use FKSDB\Models\WebService\XMLHelper;
use Nette\Application\BadRequestException;
use Nette\Http\IResponse;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

/**
 * @phpstan-extends WebModel<array{event_id?:int,eventId:int},array{
 *     teams?: mixed,
 *     participants?:mixed,
 *     schedule?:mixed,
 *     personSchedule?:mixed,
 * }>
 */
class EventWebModel extends WebModel
{

    private EventService $eventService;
    private PersonScheduleService $personScheduleService;

    public function inject(EventService $eventService, PersonScheduleService $personScheduleService): void
    {
        $this->eventService = $eventService;
        $this->personScheduleService = $personScheduleService;
    }

    /**
     * @throws \SoapFault
     * @throws \DOMException
     */
    public function getResponse(\stdClass $args): \SoapVar
    {
        if (!isset($args->eventId)) {
            throw new \SoapFault('Sender', 'Unknown eventId.');
        }
        $event = $this->eventService->findByPrimary($args->eventId);
        if (is_null($event)) {
            throw new \SoapFault('Sender', 'Unknown event.');
        }
        $doc = new \DOMDocument();
        $root = $this->createEventDetailNode($doc, $event);
        $root->appendChild($this->createParticipantListNode($doc, $event));
        $doc->formatOutput = true;
        return new \SoapVar($doc->saveXML($root), XSD_ANYXML);
    }

    private function createPersonScheduleArray(EventModel $event): array
    {
        $data = [];
        $query = $this->personScheduleService->getTable()
            ->where('schedule_item.schedule_group.event_id', $event->event_id);
        /** @var PersonScheduleModel $model */
        foreach ($query as $model) {
            $data[] = [
                'person' => [
                    'name' => $model->person->getFullName(),
                    'personId' => $model->person_id,
                    'email' => $model->person->getInfo()->email,
                ],
                'scheduleItemId' => $model->schedule_item_id,
            ];
        }
        return $data;
    }

    /**
     * @throws \Exception
     * @phpstan-import-type SerializedScheduleGroupModel from ScheduleGroupModel
     * @phpstan-import-type SerializedScheduleItemModel from ScheduleItemModel
     * @phpstan-return (SerializedScheduleGroupModel&array{
     *     scheduleItems:SerializedScheduleItemModel[],
     *     schedule_items:SerializedScheduleItemModel[],
     *  })[]
     */
    private function createScheduleListArray(EventModel $event): array
    {
        $data = [];
        /** @var ScheduleGroupModel $group */
        foreach ($event->getScheduleGroups() as $group) {
            $datum = $group->__toArray();
            $datum['schedule_items'] = [];
            $datum['scheduleItems'] = [];
            /** @var ScheduleItemModel $item */
            foreach ($group->getItems() as $item) {
                $datum['schedule_items'][] = $item->__toArray();
                $datum['scheduleItems'][] = $item->__toArray();
            }
            $data[] = $datum;
        }
        return $data;
    }

    /**
     * @throws \DOMException
     */
    public function createEventDetailNode(\DOMDocument $doc, EventModel $event): \DOMElement
    {
        $rootNode = $doc->createElement('eventDetail');
        $rootNode->appendChild($event->createXMLNode($doc));
        return $rootNode;
    }

    private function createTeamListArray(EventModel $event): array
    {
        $teamsData = [];
        /** @var TeamModel2 $team */
        foreach ($event->getTeams() as $team) {
            $teamData = [
                'teamId' => $team->fyziklani_team_id,
                'name' => $team->name,
                'status' => $team->state->value,
                'category' => $team->category->value,
                'created' => $team->created->format('c'),
                'phone' => $team->phone,
                'password' => $team->password,
                'points' => $team->points,
                'rankCategory' => $team->rank_category,
                'rankTotal' => $team->rank_total,
                'forceA' => $team->force_a,
                'gameLang' => $team->game_lang->value,
                'teachers' => [],
                'members' => [],
            ];
            /** @var TeamTeacherModel $teacher */
            foreach ($team->getTeachers() as $teacher) {
                $teamData['teachers'][] = [
                    'name' => $teacher->person->getFullName(),
                    'personId' => $teacher->person->person_id,
                    'email' => $teacher->person->getInfo()->email,
                ];
            }
            /** @var TeamMemberModel $member */
            foreach ($team->getMembers() as $member) {
                $teamData['members'][] = $this->createParticipantArray($member);
            }
            $teamsData[$team->fyziklani_team_id] = $teamData;
        }
        return $teamsData;
    }

    /**
     * @throws \DOMException
     */
    private function createParticipantListNode(\DOMDocument $doc, EventModel $event): \DOMElement
    {
        $rootNode = $doc->createElement('participants');
        /** @var EventParticipantModel $participant */
        foreach ($event->getParticipants() as $participant) {
            $pNode = $this->createParticipantNode($participant, $doc);
            $rootNode->appendChild($pNode);
        }
        return $rootNode;
    }

    private function createParticipantListArray(EventModel $event): array
    {
        $participants = [];
        /** @var EventParticipantModel $participant */
        foreach ($event->getParticipants() as $participant) {
            $participants[$participant->event_participant_id] = $this->createParticipantArray($participant);
        }
        return $participants;
    }

    /**
     * @throws \DOMException
     */
    private function createParticipantNode(EventParticipantModel $participant, \DOMDocument $doc): \DOMElement
    {
        $pNode = $participant->createXMLNode($doc);
        XMLHelper::fillArrayToNode($this->createParticipantArray($participant), $doc, $pNode);
        return $pNode;
    }

    /**
     * @param TeamMemberModel|EventParticipantModel $member
     */
    private function createParticipantArray($member): array
    {
        $history = $member->getPersonHistory();
        return [
            'name' => $member->person->getFullName(),
            'personId' => (string)$member->person->person_id,
            'email' => $member->person->getInfo()->email,
            'schoolId' => $history ? (string)$history->school_id : null,
            'schoolName' => $history ? $history->school->name_abbrev : null,
            'studyYear' => $history ? (string)$history->study_year_new->numeric() : null,
            'studyYearNew' => $history ? $history->study_year_new->value : null,
            'countryIso' => $history ? (
            ($school = $history->school) ? $school->address->country->alpha_2 : null
            ) : null,
        ];
    }

    /**
     * @throws BadRequestException
     * @throws \Exception
     */
    public function getJsonResponse(array $params): array
    {
        $event = $this->eventService->findByPrimary($params['event_id'] ?? $params['eventId']);
        if (is_null($event)) {
            throw new BadRequestException('Unknown event.', IResponse::S404_NOT_FOUND);
        }
        $data = $event->__toArray();
        if ($event->isTeamEvent()) {
            $data['teams'] = $this->createTeamListArray($event);
        } else {
            $data['participants'] = $this->createParticipantListArray($event);
        }
        $data['schedule'] = $this->createScheduleListArray($event);
        $data['personSchedule'] = $this->createPersonScheduleArray($event);
        return $data;
    }

    public function getExpectedParams(): Structure
    {
        return Expect::structure([
            'event_id' => Expect::scalar()->castTo('int'),
            'eventId' => Expect::scalar()->castTo('int'),
        ]);
    }
}
