<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\Models\Fyziklani\ParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use FKSDB\Models\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleGroup;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\Models\ORM\Services\Schedule\ServicePersonSchedule;
use FKSDB\Models\ORM\Services\ServiceEvent;
use FKSDB\Models\WebService\XMLHelper;
use Nette\Application\BadRequestException;
use Nette\Http\IResponse;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

class EventWebModel extends WebModel
{

    private ServiceEvent $serviceEvent;
    private ServicePersonSchedule $servicePersonSchedule;

    public function inject(ServiceEvent $serviceEvent, ServicePersonSchedule $servicePersonSchedule): void
    {
        $this->serviceEvent = $serviceEvent;
        $this->servicePersonSchedule = $servicePersonSchedule;
    }

    /**
     * @throws \SoapFault
     */
    public function getResponse(\stdClass $args): \SoapVar
    {
        if (!isset($args->eventId)) {
            throw new \SoapFault('Sender', 'Unknown eventId.');
        }
        $event = $this->serviceEvent->findByPrimary($args->eventId);
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

    private function createPersonScheduleNode(\DOMDocument $doc, ModelEvent $event): \DOMElement
    {
        $rootNode = $doc->createElement('personSchedule');

        $query = $this->servicePersonSchedule->getTable()
            ->where('schedule_item.schedule_group.event_id', $event->event_id)
            ->order('person_id');
        $lastPersonId = null;
        $currentNode = null;
        foreach ($query as $row) {
            $model = ModelPersonSchedule::createFromActiveRow($row);
            if ($lastPersonId !== $model->person_id) {
                $lastPersonId = $model->person_id;
                $currentNode = $doc->createElement('personSchedule');
                $personNode = $doc->createElement('person');
                XMLHelper::fillArrayToNode([
                    'name' => $model->getPerson()->getFullName(),
                    'personId' => $model->person_id,
                    'email' => $model->getPerson()->getInfo()->email,
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

    private function createPersonScheduleArray(ModelEvent $event): array
    {
        $data = [];
        $query = $this->servicePersonSchedule->getTable()
            ->where('schedule_item.schedule_group.event_id', $event->event_id);
        /** @var ModelPersonSchedule $model */
        foreach ($query as $model) {
            $data[] = [
                'person' => [
                    'name' => $model->getPerson()->getFullName(),
                    'personId' => $model->person_id,
                    'email' => $model->getPerson()->getInfo()->email,
                ],
                'scheduleItemId' => $model->schedule_item_id,
            ];
        }
        return $data;
    }

    private function createScheduleListNode(\DOMDocument $doc, ModelEvent $event): \DOMElement
    {
        $rootNode = $doc->createElement('schedule');
        foreach ($event->getScheduleGroups() as $row) {
            $group = ModelScheduleGroup::createFromActiveRow($row);
            $groupNode = $group->createXMLNode($doc);

            foreach ($group->getItems() as $itemRow) {
                $item = ModelScheduleItem::createFromActiveRow($itemRow);
                $groupNode->appendChild($item->createXMLNode($doc));
            }
            $rootNode->appendChild($groupNode);
        }
        return $rootNode;
    }

    private function createScheduleListArray(ModelEvent $event): array
    {
        $data = [];
        foreach ($event->getScheduleGroups() as $row) {
            $group = ModelScheduleGroup::createFromActiveRow($row);
            $datum = $group->__toArray();
            $datum['schedule_items'] = [];

            foreach ($group->getItems() as $itemRow) {
                $item = ModelScheduleItem::createFromActiveRow($itemRow);
                $datum['schedule_items'][] = $item->__toArray();
            }
            $data[] = $datum;
        }
        return $data;
    }

    public function createEventDetailNode(\DOMDocument $doc, ModelEvent $event): \DOMElement
    {
        $rootNode = $doc->createElement('eventDetail');
        $rootNode->appendChild($event->createXMLNode($doc));
        return $rootNode;
    }

    private function createTeamListNode(\DOMDocument $doc, ModelEvent $event): \DOMElement
    {
        $rootNode = $doc->createElement('teams');
        foreach ($event->getTeams() as $row) {
            $team = TeamModel::createFromActiveRow($row);
            $teacher = $team->getTeacher();
            $teamNode = $team->createXMLNode($doc);

            if ($teacher) {
                $teacherNode = $doc->createElement('teacher');
                $teacherNode->setAttribute('personId', (string)$teacher->person_id);
                XMLHelper::fillArrayToNode([
                    'name' => $teacher->getFullName(),
                    'email' => $teacher->getInfo()->email,
                ], $doc, $teacherNode);
                $teamNode->appendChild($teacherNode);
            }

            foreach ($team->getFyziklaniParticipants() as $participantRow) {
                $participant = ParticipantModel::createFromActiveRow($participantRow)->getEventParticipant();
                $pNode = $this->createParticipantNode($participant, $doc);
                $teamNode->appendChild($pNode);
            }

            $rootNode->appendChild($teamNode);
        }
        return $rootNode;
    }

    private function createTeamListArray(ModelEvent $event): array
    {
        $teamsData = [];
        foreach ($event->getTeams() as $row) {
            $team = TeamModel::createFromActiveRow($row);
            $teacher = $team->getTeacher();
            $teamData = [
                'teamId' => $team->e_fyziklani_team_id,
                'name' => $team->name,
                'status' => $team->status,
                'category' => $team->category,
                'created' => $team->created->format('c'),
                'phone' => $team->phone,
                'password' => $team->password,
                'points' => $team->points,
                'rankCategory' => $team->rank_category,
                'rankTotal' => $team->rank_total,
                'forceA' => $team->force_a,
                'gameLang' => $team->game_lang,
                'teacher' => $teacher ? [
                    'name' => $teacher->getFullName(),
                    'email' => $teacher->getInfo()->email,
                ] : null,
                'participants' => [],
            ];

            foreach ($team->getFyziklaniParticipants() as $participantRow) {
                $participant = ParticipantModel::createFromActiveRow($participantRow)->getEventParticipant();
                $teamData['participants'][] = $this->createParticipantArray($participant);
            }
            $teamsData[$team->e_fyziklani_team_id] = $teamData;
        }
        return $teamsData;
    }

    private function createParticipantListNode(\DOMDocument $doc, ModelEvent $event): \DOMElement
    {
        $rootNode = $doc->createElement('participants');
        foreach ($event->getParticipants() as $row) {
            $participant = ModelEventParticipant::createFromActiveRow($row);
            $pNode = $this->createParticipantNode($participant, $doc);
            $rootNode->appendChild($pNode);
        }
        return $rootNode;
    }

    private function createParticipantListArray(ModelEvent $event): array
    {
        $participants = [];
        foreach ($event->getParticipants() as $row) {
            $participant = ModelEventParticipant::createFromActiveRow($row);
            $participants[$participant->event_participant_id] = $this->createParticipantArray($participant);
        }
        return $participants;
    }

    private function createParticipantNode(ModelEventParticipant $participant, \DOMDocument $doc): \DOMElement
    {
        $pNode = $participant->createXMLNode($doc);
        XMLHelper::fillArrayToNode($this->createParticipantArray($participant), $doc, $pNode);
        return $pNode;
    }

    private function createParticipantArray(ModelEventParticipant $participant): array
    {
        $history = $participant->getPersonHistory();
        return [
            'name' => $participant->getPerson()->getFullName(),
            'email' => $participant->getPerson()->getInfo()->email,
            'schoolId' => $history ? $history->school_id : null,
            'schoolName' => $history ? $history->getSchool()->name_abbrev : null,
            'countryIso' => $history ? $history->getSchool()->getAddress()->getRegion()->country_iso : null,
        ];
    }

    /**
     * @throws BadRequestException
     */
    public function getJsonResponse(array $params): array
    {
        $event = $this->serviceEvent->findByPrimary($params['event_id']);
        if (is_null($event)) {
            throw new BadRequestException('Unknown event.', IResponse::S404_NOT_FOUND);
        }
        $data = $event->__toArray();
        $data['teams'] = $this->createTeamListArray($event);
        $data['participants'] = $this->createParticipantListArray($event);
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
