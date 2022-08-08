<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\WebService\XMLHelper;
use Nette\Application\BadRequestException;
use Nette\Http\IResponse;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

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

        $root->appendChild($this->createTeamListNode($doc, $event));
        $root->appendChild($this->createScheduleListNode($doc, $event));
        $root->appendChild($this->createPersonScheduleNode($doc, $event));
        $root->appendChild($this->createParticipantListNode($doc, $event));
        $doc->formatOutput = true;
        return new \SoapVar($doc->saveXML($root), XSD_ANYXML);
    }

    private function createPersonScheduleNode(\DOMDocument $doc, EventModel $event): \DOMElement
    {
        $rootNode = $doc->createElement('personSchedule');

        $query = $this->personScheduleService->getTable()
            ->where('schedule_item.schedule_group.event_id', $event->event_id)
            ->order('person_id');
        $lastPersonId = null;
        $currentNode = null;
        foreach ($query as $row) {
            $model = PersonScheduleModel::createFromActiveRow($row);
            if ($lastPersonId !== $model->person_id) {
                $lastPersonId = $model->person_id;
                $currentNode = $doc->createElement('personSchedule');
                $personNode = $doc->createElement('person');
                XMLHelper::fillArrayToNode([
                    'name' => $model->person->getFullName(),
                    'personId' => $model->person_id,
                    'email' => $model->person->getInfo()->email,
                ], $doc, $personNode);
                $currentNode->appendChild($personNode);
                $rootNode->appendChild($currentNode);
            }
            $scheduleItemNode = $doc->createElement('scheduleItemId');
            $scheduleItemNode->nodeValue = $model->schedule_item_id;
            $currentNode->appendChild($scheduleItemNode);
        }
        return $rootNode;
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

    private function createScheduleListNode(\DOMDocument $doc, EventModel $event): \DOMElement
    {
        $rootNode = $doc->createElement('schedule');
        foreach ($event->getScheduleGroups() as $row) {
            $group = ScheduleGroupModel::createFromActiveRow($row);
            $groupNode = $group->createXMLNode($doc);

            foreach ($group->getItems() as $itemRow) {
                $item = ScheduleItemModel::createFromActiveRow($itemRow);
                $groupNode->appendChild($item->createXMLNode($doc));
            }
            $rootNode->appendChild($groupNode);
        }
        return $rootNode;
    }

    private function createScheduleListArray(EventModel $event): array
    {
        $data = [];
        foreach ($event->getScheduleGroups() as $row) {
            $group = ScheduleGroupModel::createFromActiveRow($row);
            $datum = $group->__toArray();
            $datum['schedule_items'] = [];

            foreach ($group->getItems() as $itemRow) {
                $item = ScheduleItemModel::createFromActiveRow($itemRow);
                $datum['schedule_items'][] = $item->__toArray();
            }
            $data[] = $datum;
        }
        return $data;
    }

    public function createEventDetailNode(\DOMDocument $doc, EventModel $event): \DOMElement
    {
        $rootNode = $doc->createElement('eventDetail');
        $rootNode->appendChild($event->createXMLNode($doc));
        return $rootNode;
    }

    private function createTeamListNode(\DOMDocument $doc, EventModel $event): \DOMElement
    {
        $rootNode = $doc->createElement('teams');
        foreach ($event->getFyziklaniTeams() as $row) {
            $team = TeamModel2::createFromActiveRow($row);
            $teamNode = $team->createXMLNode($doc);

            foreach ($team->getTeachers() as $teacherRow) {
                $teacher = TeamTeacherModel::createFromActiveRow($teacherRow);
                $teacherNode = $doc->createElement('teacher');
                $teacherNode->setAttribute('personId', (string)$teacher->person_id);
                XMLHelper::fillArrayToNode([
                    'name' => $teacher->person->getFullName(),
                    'email' => $teacher->person->getInfo()->email,
                ], $doc, $teacherNode);
                $teamNode->appendChild($teacherNode);
            }

            foreach ($team->getMembers() as $memberRow) {
                $member = TeamMemberModel::createFromActiveRow($memberRow);
                $pNode = $this->createTeamMemberNode($member, $doc);
                $teamNode->appendChild($pNode);
            }

            $rootNode->appendChild($teamNode);
        }
        return $rootNode;
    }

    private function createTeamListArray(EventModel $event): array
    {
        $teamsData = [];
        foreach ($event->getFyziklaniTeams() as $row) {
            $team = TeamModel2::createFromActiveRow($row);
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
            foreach ($team->getTeachers() as $teacherRow) {
                $teacher = TeamTeacherModel::createFromActiveRow($teacherRow);
                $teamData['teachers'][] = [
                    'name' => $teacher->person->getFullName(),
                    'email' => $teacher->person->getInfo()->email,
                ];
            }

            foreach ($team->getMembers() as $memberRow) {
                $member = TeamMemberModel::createFromActiveRow($memberRow);
                $teamData['members'][] = $this->createParticipantArray($member);
            }
            $teamsData[$team->fyziklani_team_id] = $teamData;
        }
        return $teamsData;
    }

    private function createParticipantListNode(\DOMDocument $doc, EventModel $event): \DOMElement
    {
        $rootNode = $doc->createElement('participants');
        foreach ($event->getParticipants() as $row) {
            $participant = EventParticipantModel::createFromActiveRow($row);
            $pNode = $this->createParticipantNode($participant, $doc);
            $rootNode->appendChild($pNode);
        }
        return $rootNode;
    }

    private function createParticipantListArray(EventModel $event): array
    {
        $participants = [];
        foreach ($event->getParticipants() as $row) {
            $participant = EventParticipantModel::createFromActiveRow($row);
            $participants[$participant->event_participant_id] = $this->createParticipantArray($participant);
        }
        return $participants;
    }

    private function createParticipantNode(EventParticipantModel $participant, \DOMDocument $doc): \DOMElement
    {
        $pNode = $participant->createXMLNode($doc);
        XMLHelper::fillArrayToNode($this->createParticipantArray($participant), $doc, $pNode);
        return $pNode;
    }

    private function createTeamMemberNode(TeamMemberModel $member, \DOMDocument $doc): \DOMElement
    {
        $pNode = $member->createXMLNode($doc);
        XMLHelper::fillArrayToNode($this->createParticipantArray($member), $doc, $pNode);
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
            'email' => $member->person->getInfo()->email,
            'schoolId' => $history ? $history->school_id : null,
            'schoolName' => $history ? $history->school->name_abbrev : null,
            'countryIso' => $history ? (
            ($school = $history->school)
                ? $school->address->region->country_iso
                : null
            ) : null,
        ];
    }

    /**
     * @throws BadRequestException
     * #Array
     */
    public function getJsonResponse(array $params): array
    {
        $event = $this->eventService->findByPrimary($params['event_id']);
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
        $data['person_schedule'] = $this->createPersonScheduleArray($event);
        return $data;
    }

    public function getExpectedParams(): Structure
    {
        return Expect::structure([
            'event_id' => Expect::scalar()->castTo('int')->required(),
        ]);
    }
}
