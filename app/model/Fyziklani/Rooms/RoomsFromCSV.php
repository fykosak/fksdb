<?php

namespace FKSDB\model\Fyziklani\Rooms;

use FKS\Logging\ILogger;
use FKS\Utils\CSVParser;
use ModelEvent;
use ORM\Services\Events\ServiceFyziklaniTeam;
use Pipeline\PipelineException;
use Pipeline\Stage;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class RoomsFromCSV extends Stage {

    /**
     * @var string
     */
    private $data;

    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceTeam;

    function __construct(ModelEvent $event, ServiceFyziklaniTeam $serviceTeam) {
        $this->event = $event;
        $this->serviceTeam = $serviceTeam;
    }

    public function setInput($data) {
        $this->data = $data;
    }

    public function process() {
        if (!file_exists($this->data)) {
            throw new PipelineException(sprintf('File %s doesn\'t exist.', $this->data));
        }

        $teams = $this->serviceTeam->getTable()
                ->where('event_id', $this->event->event_id)
                ->where('status!=?', 'cancelled')
                ->fetchPairs('e_fyziklani_team_id');
        $updatedTeams = array();

        $this->serviceTeam->getConnection()->beginTransaction();
        $parser = new CSVParser($this->data);
        foreach ($parser as $row) {
            $teamId = $row[0];
            $room = $row[1];

            if (!array_key_exists($teamId, $teams)) {
                $this->getPipeline()->log(sprintf(_('Přeskočeno neexistující ID týmu %d.'), $teamId), ILogger::WARNING);
                continue;
            }
            $team = $teams[$teamId];
            $this->serviceTeam->updateModel($team, [
                'room' => $room,
            ]);
            $this->serviceTeam->save($team);
            $updatedTeams[$teamId] = $team;
            if ($room) {
                unset($teams[$teamId]);
            }
        }
        $this->serviceTeam->getConnection()->commit();

        foreach ($teams as $team) {
            $this->getPipeline()->log(sprintf(_('Tým %s (%d, %s) nemá přiřazenou místnost.'), $team->name, $team->e_fyziklani_team_id, $team->status), ILogger::WARNING);
        }
    }

    public function getOutput() {
        return null;
    }

}
