<?php

namespace FKSDB\ORM\Services\Fyziklani;

use AbstractServiceSingle;
use DbNames;
use FKSDB\ORM\ModelEvent;

/**
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServiceFyziklaniTeam extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_E_FYZIKLANI_TEAM;

    protected $modelClassName = 'FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam';

    /**
     * Syntactic sugar.
     * @param ModelEvent $event
     * @return \Nette\Database\Table\Selection|null
     */
    public function findParticipating(ModelEvent $event) {
        $result = $this->getTable()->where('status', 'participated')->where('event_id', $event->event_id);;
        return $result ?: null;
    }

    /**
     * @param int $teamId
     * @param ModelEvent $event
     * @return bool
     */
    public function teamExist(int $teamId, ModelEvent $event): bool {
        /**
         * @var \FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam $team
         */
        $row = $this->findByPrimary($teamId);
        if (!$row) {
            return false;
        }
        $team = \FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam::createFromTableRow($row);
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
    public function getTeamsAsArray(ModelEvent $event): array {
        $teams = [];

        foreach ($this->findPossiblyAttending($event) as $row) {
            $team = \FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam::createFromTableRow($row);
            $teams[] = $team->__toArray(true);
        }
        return $teams;
    }

}
