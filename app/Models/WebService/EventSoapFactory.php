<?php

namespace FKSDB\Models\WebService;

use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use FKSDB\Models\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleGroup;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\Models\ORM\Services\Schedule\ServicePersonSchedule;
use FKSDB\Models\ORM\Services\ServiceEvent;
use FKSDB\Models\ORM\Services\ServiceEventParticipant;
use Nette\SmartObject;
use SoapVar;

/**
 * Class FyziklaniSoapFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
class EventSoapFactory {
    use SmartObject;

    private ServiceEvent $serviceEvent;
    private ServicePersonSchedule $servicePersonSchedule;
    private ServiceEventParticipant $serviceEventParticipant;

    public function __construct(ServiceEvent $serviceEvent, ServicePersonSchedule $servicePersonSchedule, ServiceEventParticipant $serviceEventParticipant) {
        $this->serviceEvent = $serviceEvent;
        $this->servicePersonSchedule = $servicePersonSchedule;
        $this->serviceEventParticipant = $serviceEventParticipant;
    }

    /**
     * @param \stdClass $args
     * @return SoapVar
     * @throws \SoapFault
     */
    public function handleGetEvent(\stdClass $args): SoapVar {
        if (!isset($args->eventId)) {
            throw new \SoapFault('Sender', 'Unknown event.');
        }
        $event = $this->serviceEvent->findByPrimary($args->eventId);
        if (is_null($event)) {
            throw new \SoapFault('Sender', 'Unknown event.');
        }
        $doc = new \DOMDocument();

        if (isset($args->teamsList)) {
            $this->createTeamList($doc, $args, $event);
        }
        if (isset($args->schedule)) {
            $this->createScheduleNode($doc, $args, $event);
        }
        if (isset($args->personSchedule)) {
            $this->createPersonScheduleNode($doc, $args, $event);
        }
        if (isset($args->participantsList)) {
            $this->createPersonScheduleNode($doc, $args, $event);
        }

        $doc->formatOutput = true;

        $nodeString = '';
        foreach ($doc->childNodes as $node) {
            $nodeString .= $doc->saveXML($node);
        }
        return new SoapVar($nodeString, XSD_ANYXML);
    }

    /**
     * @param \stdClass $args
     * @return SoapVar
     * @throws \SoapFault
     */
    public function handleGetEventsList(\stdClass $args): SoapVar {
        if (!isset($args->eventTypeIds)) {
            throw new \SoapFault('Sender', 'Unknown eventType.');
        }
        $query = $this->serviceEvent->getTable()->where('event_type_id', (array)$args->eventTypeIds);
        $doc = new \DOMDocument();
        $doc->formatOutput = true;
        $rootNode = $doc->createElement('events');
        /** @var ModelEvent $event */
        foreach ($query as $event) {
            $rootNode->appendChild($event->createXMLNode($doc));
        }
        return new SoapVar($doc->saveXML($rootNode), XSD_ANYXML);
    }

    private function createPersonScheduleNode(\DOMDocument $doc, \stdClass $args, ModelEvent $event): void {
        $rootNode = $doc->createElement('personSchedule');

        $query = $this->servicePersonSchedule->getTable()
            ->where('schedule_item.schedule_group.event_id', $event->event_id);
        if (isset($args->personSchedule) && isset($args->personSchedule->groupType)) {
            $query->where('schedule_item.schedule_group.schedule_group_type', $args->personSchedule->groupType);
        }
        $query->order('person_id');
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
        $doc->appendChild($rootNode);
    }

    private function createScheduleNode(\DOMDocument $doc, \stdClass $args, ModelEvent $event): void {
        $rootNode = $doc->createElement('schedule');
        $query = $event->getScheduleGroups();
        if (isset($args->schedule) && isset($args->schedule->groupType)) {
            $query->where('schedule_group_type', $args->schedule->groupType);
        }
        foreach ($query as $row) {
            $group = ModelScheduleGroup::createFromActiveRow($row);
            $groupNode = $group->createXMLNode($doc);

            foreach ($group->getItems() as $itemRow) {
                $item = ModelScheduleItem::createFromActiveRow($itemRow);
                $groupNode->appendChild($item->createXMLNode($doc));
            }
            $rootNode->appendChild($groupNode);
        }
        $doc->appendChild($rootNode);
    }

    /**
     * @param \DOMDocument $doc
     * @param \stdClass $args
     * @param ModelEvent $event
     * @return void
     * @throws \SoapFault
     */
    private function createTeamList(\DOMDocument $doc, \stdClass $args, ModelEvent $event): void {
        if (!$event->isTeamEvent()) {
            throw new \SoapFault('Sender', 'Wrong event type.');
        }
        $rootNode = $doc->createElement('teamList');
        $query = $event->getTeams();
        if (isset($args->teamList) && isset($args->teamList->status)) {
            $query->where('status', $args->teamList->status);
        }
        foreach ($query as $row) {
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
        $doc->appendChild($rootNode);
    }

    private function handleParticipantList(\stdClass $args, ModelEvent $event): SoapVar {
        $doc = new \DOMDocument();
        $rootNode = $doc->createElement('participantList');
        /** @var ModelEventParticipant $participant */
        foreach ($this->serviceEventParticipant->findByEvent($event) as $participant) {
            $pNode = $this->createParticipantNode($participant, $doc);
            $rootNode->appendChild($pNode);
        }
        $doc->appendChild($rootNode);
        $doc->formatOutput = true;
        return new SoapVar($doc->saveXML($rootNode), XSD_ANYXML);
    }

    private function createParticipantNode(ModelEventParticipant $participant, \DOMDocument $doc): \DOMElement {
        $pNode = $participant->createXMLNode($doc);
        XMLHelper::fillArrayToNode([
            'name' => $participant->getPerson()->getFullName(),
            'email' => $participant->getPerson()->getInfo()->email,
            'schoolId' => $participant->getPersonHistory()->school_id,
            'schoolName' => $participant->getPersonHistory()->getSchool()->name_abbrev,
            'countryIso' => $participant->getPersonHistory()->getSchool()->getAddress()->getRegion()->country_iso,
        ], $doc, $pNode);
        return $pNode;
    }
}
