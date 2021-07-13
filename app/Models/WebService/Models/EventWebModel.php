<?php

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use FKSDB\Models\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleGroup;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\Models\ORM\Services\Schedule\ServicePersonSchedule;
use FKSDB\Models\ORM\Services\ServiceEvent;
use FKSDB\Models\WebService\XMLHelper;

class EventWebModel extends WebModel {

    private ServiceEvent $serviceEvent;
    private ServicePersonSchedule $servicePersonSchedule;

    public function inject(ServiceEvent $serviceEvent, ServicePersonSchedule $servicePersonSchedule): void {
        $this->serviceEvent = $serviceEvent;
        $this->servicePersonSchedule = $servicePersonSchedule;
    }

    /**
     * @param \stdClass $args
     * @return \SoapVar
     * @throws \SoapFault
     */
    public function getResponse(\stdClass $args): \SoapVar {
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

    private function createPersonScheduleNode(\DOMDocument $doc, ModelEvent $event): \DOMElement {
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

    private function createScheduleListNode(\DOMDocument $doc, ModelEvent $event): \DOMElement {
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

    public function createEventDetailNode(\DOMDocument $doc, ModelEvent $event): \DOMElement {
        $rootNode = $doc->createElement('eventDetail');
        $rootNode->appendChild($event->createXMLNode($doc));
        return $rootNode;
    }

    private function createTeamListNode(\DOMDocument $doc, ModelEvent $event): \DOMElement {
        $rootNode = $doc->createElement('teams');
        foreach ($event->getTeams() as $row) {
            $team = ModelFyziklaniTeam::createFromActiveRow($row);
            $teacher = $team->getTeacher();
            $teamNode = $team->createXMLNode($doc);

            if ($teacher) {
                $teacherNode = $doc->createElement('teacher');
                $teacherNode->setAttribute('personId', $teacher->person_id);
                XMLHelper::fillArrayToNode([
                    'name' => $teacher->getFullName(),
                    'email' => $teacher->getInfo()->email,
                ], $doc, $teacherNode);
                $teamNode->appendChild($teacherNode);
            }

            foreach ($team->getParticipants() as $participantRow) {
                $participant = ModelEventParticipant::createFromActiveRow($participantRow->event_participant);
                $pNode = $this->createParticipantNode($participant, $doc);
                $teamNode->appendChild($pNode);
            }

            $rootNode->appendChild($teamNode);
        }
        return $rootNode;
    }

    private function createParticipantListNode(\DOMDocument $doc, ModelEvent $event): \DOMElement {
        $rootNode = $doc->createElement('participants');
        foreach ($event->getParticipants() as $row) {
            $participant = ModelEventParticipant::createFromActiveRow($row);
            $pNode = $this->createParticipantNode($participant, $doc);
            $rootNode->appendChild($pNode);
        }
        return $rootNode;
    }

    private function createParticipantNode(ModelEventParticipant $participant, \DOMDocument $doc): \DOMElement {
        $pNode = $participant->createXMLNode($doc);
        $history = $participant->getPersonHistory();
        XMLHelper::fillArrayToNode([
            'name' => $participant->getPerson()->getFullName(),
            'email' => $participant->getPerson()->getInfo()->email,
            'schoolId' => $history ? $history->school_id : null,
            'schoolName' => $history ? $history->getSchool()->name_abbrev : null,
            'countryIso' => $history ? $history->getSchool()->getAddress()->getRegion()->country_iso : null,
        ], $doc, $pNode);
        return $pNode;
    }
}
