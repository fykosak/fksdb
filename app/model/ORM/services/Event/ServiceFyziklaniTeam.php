<?php

namespace ORM\Services\Events;

use AbstractServiceSingle;
use DbNames;

/**
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServiceFyziklaniTeam extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_E_FYZIKLANI_TEAM;

    protected $modelClassName = 'ORM\Models\Events\ModelFyziklaniTeam';

    /**
     * Syntactic sugar.
     * 
     * @return \Nette\Database\Table\Selection|null
     */
    public function findParticipating($eventId) {
        $result = $this->getTable()->where('status', 'participated');
        if ($eventId) {
            $result->where('event_id', $eventId);
        }
        return $result ?: null;
    }

    public function teamExist($teamID, $eventID) {
        $team = $this->findByPrimary($teamID);
        return $team && $team->event_id == $eventID;
    }

    public function isOpenSubmit($teamID) {
        $points = $this->findByPrimary($teamID)->points;
        return !is_numeric($points);
    }

}
