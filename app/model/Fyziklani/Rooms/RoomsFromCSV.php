<?php

namespace FKSDB\model\Fyziklani\Rooms;

use FKSDB\Logging\ILogger;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\Utils\CSVParser;
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
     * @var \FKSDB\ORM\Models\ModelEvent
     */
    private $event;

    /**
     * @var \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam
     */
    private $serviceTeam;

    /**
     * RoomsFromCSV constructor.
     * @param \FKSDB\ORM\Models\ModelEvent $event
     * @param \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam $serviceTeam
     */
    function __construct(ModelEvent $event, ServiceFyziklaniTeam $serviceTeam) {
        $this->event = $event;
        $this->serviceTeam = $serviceTeam;
    }

    /**
     * @param mixed $data
     */
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
        $updatedTeams = [];

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

    /**
     * @return mixed|null
     */
    public function getOutput() {
        return null;
    }

}
