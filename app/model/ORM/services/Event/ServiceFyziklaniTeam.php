<?php

namespace ORM\Services\Events;

use AbstractServiceSingle;
use DbNames;
use ORM\Models\Events\ModelFyziklaniTeam;

/**
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServiceFyziklaniTeam extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_E_FYZIKLANI_TEAM;

    protected $modelClassName = 'ORM\Models\Events\ModelFyziklaniTeam';

    /**
     * Syntactic sugar.
     * @param int $eventId
     * @return \Nette\Database\Table\Selection|null
     */
    public function findParticipating($eventId) {
        $result = $this->getTable()->where('status', 'participated');
        if ($eventId) {
            $result->where('event_id', $eventId);
        }
        return $result ?: null;
    }

    public function teamExist($teamId, $eventId) {
        /**
         * @var $team ModelFyziklaniTeam
         */
        $team = $this->findByPrimary($teamId);
        return $team && $team->event_id == $eventId;
    }
    /**
     * Syntactic sugar.
     * @param int $eventId
     * @return \Nette\Database\Table\Selection|null
     */
    public function findApplied($eventId = null) {
        $result = $this->getTable()->where('status', ['participated', 'approved', 'spare']);
        if ($eventId) {
            $result->where('event_id', $eventId);
        }
        return $result ?: null;
    }

    public function getTeams($eventId) {
        $teams = [];
        /**
         * @var $row ModelFyziklaniTeam
         */
        foreach ($this->findApplied($eventId) as $row) {
            /**
             * @var $row ModelFyziklaniTeam
             */
            $position = $row->getPosition();

            $teams[] = [
                'category' => $row->category,
                'roomId' => $position ? $position->getRoom()->room_id : '',
                'name' => $row->name,
                'status'=>$row->status,
                'teamId' => $row->e_fyziklani_team_id,
                'x' => $position ? $position->col : null,
                'y' => $position ? $position->row : null,
            ];
        }
        return $teams;
    }

}
