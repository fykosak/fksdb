<?php

namespace FKSDB\Models\Fyziklani\Rooms;

use FKSDB\Models\Logging\Logger;
use FKSDB\Models\Messages\Message;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\Models\Utils\CSVParser;
use FKSDB\Models\Pipeline\PipelineException;
use FKSDB\Models\Pipeline\Stage;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class RoomsFromCSV extends Stage {

    /** @var string */
    private $data;

    private ModelEvent $event;

    private ServiceFyziklaniTeam $serviceTeam;

    public function __construct(ModelEvent $event, ServiceFyziklaniTeam $serviceTeam) {
        $this->event = $event;
        $this->serviceTeam = $serviceTeam;
    }

    /**
     * @param mixed $data
     */
    public function setInput($data): void {
        $this->data = $data;
    }

    public function process(): void {
        if (!file_exists($this->data)) {
            throw new PipelineException(sprintf('File %s doesn\'t exist.', $this->data));
        }

        $teams = $this->serviceTeam->getTable()
            ->where('event_id', $this->event->event_id)
            ->where('status!=?', 'cancelled')
            ->fetchPairs('e_fyziklani_team_id');
        $updatedTeams = [];

        $this->serviceTeam->explorer->getConnection()->beginTransaction();
        $parser = new CSVParser($this->data);
        foreach ($parser as $row) {
            $teamId = $row[0];
            $room = $row[1];

            if (!array_key_exists($teamId, $teams)) {

                $this->getPipeline()->log(new Message(sprintf(_('Nonexistent team ID %d skipped'), $teamId), Logger::WARNING));

                continue;
            }
            $team = $teams[$teamId];
            $this->serviceTeam->updateModel($team, [
                'room' => $room,
            ]);
            //  $this->serviceTeam->save($team);
            $updatedTeams[$teamId] = $team;
            if ($room) {
                unset($teams[$teamId]);
            }
        }
        $this->serviceTeam->explorer->getConnection()->commit();

        foreach ($teams as $team) {
            $this->getPipeline()->log(new Message(sprintf(_('Team %s (%d, %s) does not have an assigned room.'), $team->name, $team->e_fyziklani_team_id, $team->status), Logger::WARNING));
        }
    }

    /**
     * @return mixed|null
     */
    public function getOutput() {
        return null;
    }

}

