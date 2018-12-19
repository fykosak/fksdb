<?php

namespace ORM\Services\Events;

use AbstractServiceSingle;
use DbNames;
use FKSDB\ORM\ModelEvent;
use ORM\Models\Events\ModelFyziklaniTeam;

/**
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServiceFyziklaniTeam extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_E_FYZIKLANI_TEAM;

    protected $modelClassName = 'ORM\Models\Events\ModelFyziklaniTeam';

    /**
     * Syntactic sugar.
     * @param ModelEvent $event
     * @return \Nette\Database\Table\Selection|null
     */
    public function findParticipating(ModelEvent $event) {
        $result = $this->getTable()->where('status', 'participated')->where('event_id', $event->event_id);;
        return $result ?: null;
    }

    public function teamExist(int $teamId, ModelEvent $event) {
        /**
         * @var $team ModelFyziklaniTeam
         */
        $team = $this->findByPrimary($teamId);
        return $team && $team->event_id == $event->event_id;
    }

    /**
     * Syntactic sugar.
     * @param ModelEvent $event
     * @return \Nette\Database\Table\Selection|null
     */
    public function findPossiblyAttending(ModelEvent $event) {
        $result = $this->getTable()->where('status', ['participated', 'approved', 'spare'])->where('event_id', $event->event_id);
        return $result ?: null;
    }

    /**
     * @param ModelEvent $event
     * @return array
     */
    public function getTeamsArray(ModelEvent $event) {
        $teams = [];
        /**
         * @var $row ModelFyziklaniTeam
         */
        foreach ($this->findPossiblyAttending($event) as $row) {
            /**
             * @var $row ModelFyziklaniTeam
             */
            $position = $row->getPosition();

            $teams[] = [
                'category' => $row->category,
                'roomId' => $position ? $position->getRoom()->room_id : '',
                'name' => $row->name,
                'status' => $row->status,
                'teamId' => $row->e_fyziklani_team_id,
                'x' => $position ? $position->col : null,
                'y' => $position ? $position->row : null,
            ];
        }
        return $teams;
    }

}
