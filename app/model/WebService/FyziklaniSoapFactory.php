<?php

namespace FKSDB\WebService;

use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\ServiceEvent;
use Nette\SmartObject;
use Nette\Utils\DateTime;
use SoapVar;

/**
 * Class FyziklaniSoapFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
class FyziklaniSoapFactory {
    use SmartObject;

    private ServiceEvent $serviceEvent;

    public function __construct(ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
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
        if (!$event->isTeamEvent()) {
            throw new \SoapFault('Sender', 'Unknown event.');
        }

        if (isset($args->teamList)) {
            return $this->handleTeamList($args, $event);
        }
        if (isset($args->results)) {
            return $this->handleResults($args, $event);
        }
        throw new \SoapFault('Sender', 'Unknown action.');
    }

    private function handleTeamList(\stdClass $args, ModelEvent $event): SoapVar {
        $doc = new \DOMDocument();
        $rootNode = $doc->createElement('teamList');

        foreach ($event->getTeams() as $row) {
            $team = ModelFyziklaniTeam::createFromActiveRow($row);
            $teamNode = $doc->createElement('team');
            $this->fillNode([
                'teamId' => 'e_fyziklani_team_id',
                'category' => 'category',
                'name' => 'name',
                'lang' => 'game_lang',
                'status' => 'status',
                'password' => 'password',
            ], $doc, $team, $teamNode);

// from FOL fksdb_views
            $query = $this->serviceEvent->getContext()->query('
            SELECT
                ep.event_participant_id,
                ph.school_id ,
                vp.name,
                s.name_abbrev,
                sr.country_iso
            FROM fksdb.event_participant ep
            LEFT JOIN fksdb.e_fyziklani_participant efp ON efp.event_participant_id = ep.event_participant_id
            LEFT JOIN fksdb.v_person vp on vp.person_id = ep.person_id
            LEFT JOIN fksdb.person_history ph on ph.person_id = ep.person_id AND ph.ac_year = 2019 -- UPDATE HERE
            LEFT JOIN fksdb.school s on s.school_id = ph.school_id
            LEFT JOIN fksdb.address sa on sa.address_id = s.address_id
            LEFT JOIN fksdb.region sr on sr.region_id = sa.region_id
        WHERE efp.e_fyziklani_team_id = ?;', $team->e_fyziklani_team_id);

            foreach ($query as $pRow) {
                $pNode = $doc->createElement('participant');
                $this->fillNode([
                    'participantId' => 'event_participant_id',
                    'schoolId' => 'school_id',
                    'name' => 'name',
                    'schoolName' => 'name_abbrev',
                    'countryIso' => 'country_iso',
                ], $doc, $pRow, $pNode);
                $teamNode->appendChild($pNode);
            }

            $rootNode->appendChild($teamNode);
        }

        $doc->appendChild($rootNode);
        $doc->formatOutput = true;
        return new SoapVar($doc->saveXML($rootNode), XSD_ANYXML);
    }

    private function handleResults(\stdClass $args, ModelEvent $event): SoapVar {
        $doc = new \DOMDocument();
        $rootNode = $doc->createElement('teamList');
        $gameSetup = $event->getFyziklaniGameSetup();

        $result = [
            'availablePoints' => $gameSetup->getAvailablePoints(),
            'gameStart' => $gameSetup->game_start->format('c'),
            'gameEnd' => $gameSetup->game_end->format('c'),
            'times' => [
                'toStart' => strtotime($gameSetup->game_start) - time(),
                'toEnd' => strtotime($gameSetup->game_end) - time(),
                'visible' => $gameSetup->isResultsVisible(),
            ],
            'lastUpdated' => (new DateTime())->format('c'),
            'refreshDelay' => $gameSetup->refresh_delay,
            'tasksOnBoard' => $gameSetup->tasks_on_board,
            'submits' => [],
        ];

        if ($gameSetup->isResultsVisible()) {
            $result['submits'] = $this->serviceFyziklaniSubmit->getSubmitsAsArray($event, $args->lastUpdated);
        }
        // probably need refresh before competition started
        //if (!$this->lastUpdated) {
        $result['teams'] = $this->serviceFyziklaniTeam->getTeamsAsArray($event);
        $result['tasks'] = $this->serviceFyziklaniTask->getTasksAsArray($event);
        $result['categories'] = ['A', 'B', 'C'];
        //  }
        $doc->formatOutput = true;
        return new SoapVar($doc->saveXML($rootNode), XSD_ANYXML);
    }

    private function fillNode(array $keyMap, \DOMDocument $doc, object $object, \DOMElement $parentNode): void {
        foreach ($keyMap as $key => $value) {
            $teamAttrNode = $doc->createElement($key);
            $teamAttrNode->nodeValue = $object->{$value};
            $parentNode->appendChild($teamAttrNode);
        }
    }
}
