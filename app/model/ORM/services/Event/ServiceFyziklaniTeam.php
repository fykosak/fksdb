<?php

namespace ORM\Services\Events;

use AbstractServiceSingle;
use DbNames;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
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
        $result = $this->getTable()->where('status','participated');
        if ($eventId) {
            $result->where('event_id', $eventId);
        }        
        return $result ? : null;
    }

}

