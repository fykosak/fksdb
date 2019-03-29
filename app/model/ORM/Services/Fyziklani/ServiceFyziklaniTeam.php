<?php

namespace FKSDB\ORM\Services\Fyziklani;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEvent;

/**
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServiceFyziklaniTeam extends AbstractServiceSingle {
    /**
     * @return string
     */
    protected function getModelClassName(): string {
        return ModelFyziklaniTeam::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_E_FYZIKLANI_TEAM;
    }

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
         * @var ModelFyziklaniTeam $team
         */
        $row = $this->findByPrimary($teamId);
        if (!$row) {
            return false;
        }
        $team = ModelFyziklaniTeam::createFromTableRow($row);
        return $team && $team->event_id == $event->event_id;
    }

    /**
     * Syntactic sugar.
     * @param \FKSDB\ORM\Models\ModelEvent $event
     * @return \Nette\Database\Table\Selection|null
     */
    public function findPossiblyAttending(ModelEvent $event) {
        $result = $this->getTable()->where('status', ['participated', 'approved', 'spare'])->where('event_id', $event->event_id);
        return $result ?: null;
    }

    /**
     * @param \FKSDB\ORM\Models\ModelEvent $event
     * @return array
     */
    public function getTeamsAsArray(ModelEvent $event): array {
        $teams = [];

        foreach ($this->findPossiblyAttending($event) as $row) {
            $team = ModelFyziklaniTeam::createFromTableRow($row);
            $teams[] = $team->__toArray(true);
        }
        return $teams;
    }

}
