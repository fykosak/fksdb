<?php

namespace FKSDB\WebService;

use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\ORM\Models\Schedule\ModelScheduleGroup;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\ORM\Services\Schedule\ServicePersonSchedule;
use FKSDB\ORM\Services\ServiceEvent;
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

    public function __construct(ServiceEvent $serviceEvent, ServicePersonSchedule $servicePersonSchedule) {
        $this->serviceEvent = $serviceEvent;
        $this->servicePersonSchedule = $servicePersonSchedule;
    }

    /**
     * @param \stdClass $args
     * @return SoapVar
     * @throws \SoapFault
     */
    public function handle(\stdClass $args): SoapVar {

        if (!isset($args->eventId)) {
            throw new \SoapFault('Sender', 'Unknown event.');
        }
        $event = $this->serviceEvent->findByPrimary($args->eventId);
        if (is_null($event)) {
            throw new \SoapFault('Sender', 'Unknown event.');
        }
        $doc = new \DOMDocument();

        if (isset($args->teamList)) {
            $this->createTeamList($doc, $args, $event);
        }
        if (isset($args->schedule)) {
            $this->createScheduleNode($doc, $args, $event);
        }
        if (isset($args->personSchedule)) {
            $this->createPersonScheduleNode($doc, $args, $event);
        }

        $doc->formatOutput = true;
        return new SoapVar($doc->saveXML(), XSD_ANYXML);
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
        $query=$event->getTeams();
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

// from FOL fksdb_views
            $query = $this->serviceEvent->getContext()->query('
            SELECT
                ep.event_participant_id,
                ph.school_id ,
                vp.email,
                vp.name,
                s.name_abbrev,
                sr.country_iso
            FROM fksdb.event_participant ep
            LEFT JOIN fksdb.e_fyziklani_participant efp ON efp.event_participant_id = ep.event_participant_id
            LEFT JOIN fksdb.v_person vp on vp.person_id = ep.person_id
            LEFT JOIN fksdb.event e2 on ep.event_id = e2.event_id
            LEFT JOIN fksdb.event_type et on e2.event_type_id = et.event_type_id
            LEFT JOIN fksdb.contest_year cy on e2.year = cy.year and cy.contest_id = et.contest_id
            LEFT JOIN fksdb.person_history ph on ph.person_id = ep.person_id AND ph.ac_year = cy.ac_year
            LEFT JOIN fksdb.school s on s.school_id = ph.school_id
            LEFT JOIN fksdb.address sa on sa.address_id = s.address_id
            LEFT JOIN fksdb.region sr on sr.region_id = sa.region_id
        WHERE efp.e_fyziklani_team_id = ?;', $team->e_fyziklani_team_id);

            foreach ($query as $pRow) {
                $pNode = $doc->createElement('participant');
                $pNode->setAttribute('participantId', $pRow->event_participant_id);
                XMLHelper::fillArrayToNode([
                    'participantId' => $pRow->event_participant_id,
                    'schoolId' => $pRow->school_id,
                    'name' => $pRow->name,
                    'email' => $pRow->email,
                    'schoolName' => $pRow->name_abbrev,
                    'countryIso' => $pRow->country_iso,
                ], $doc, $pNode);
                $teamNode->appendChild($pNode);
            }
            $rootNode->appendChild($teamNode);
        }
        $doc->appendChild($rootNode);
    }

    /* private function handleParticipantList(\stdClass $args, ModelEvent $event): SoapVar {
         $doc = new \DOMDocument();
         $rootNode = $doc->createElement('participantList');

 // from FOL fksdb_views
         $query = $this->serviceEvent->getContext()->query('
             SELECT ep.event_participant_id,
        ph.school_id,
        vp.email,
        vp.name,
        s.name_abbrev,
        sr.country_iso
 FROM fksdb.event_participant ep
          LEFT JOIN fksdb.v_person vp on vp.person_id = ep.person_id
          LEFT JOIN event e2 on ep.event_id = e2.event_id
          LEFT JOIN event_type et on e2.event_type_id = et.event_type_id
          LEFT JOIN contest_year cy on e2.year = cy.year and cy.contest_id = et.contest_id
          LEFT JOIN fksdb.person_history ph on ph.person_id = ep.person_id AND ph.ac_year = cy.ac_year
          LEFT JOIN fksdb.school s on s.school_id = ph.school_id
          LEFT JOIN fksdb.address sa on sa.address_id = s.address_id
          LEFT JOIN fksdb.region sr on sr.region_id = sa.region_id
 WHERE ep.event_id = ?', $event->event_id);
         foreach ($query as $pRow) {
             $pNode = $doc->createElement('participant');
             $this->fillNode([
                 'participantId' => 'event_participant_id',
                 'schoolId' => 'school_id',
                 'name' => 'name',
                 'email' => 'email',
                 'schoolName' => 'name_abbrev',
                 'countryIso' => 'country_iso',
             ], $doc, $pRow, $pNode);
             $rootNode->appendChild($pNode);
         }

         $doc->appendChild($rootNode);
         $doc->formatOutput = true;
         return new SoapVar($doc->saveXML($rootNode), XSD_ANYXML);
     }*/

}
