<?php

namespace FKSDB\ORM\Services\Fyziklani;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Tables\TypedTableSelection;

/**
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServiceFyziklaniTeam extends AbstractServiceSingle {

    public function getModelClassName(): string {
        return ModelFyziklaniTeam::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_E_FYZIKLANI_TEAM;
    }

    public function findParticipating(ModelEvent $event): TypedTableSelection {
        return $this->getTable()->where('status', 'participated')->where('event_id', $event->event_id);
    }

    public function teamExist(int $teamId, ModelEvent $event): bool {
        $row = $this->findByPrimary($teamId);
        if (!$row) {
            return false;
        }
        $team = ModelFyziklaniTeam::createFromActiveRow($row);
        return $team && $team->event_id == $event->event_id;
    }

    public function findPossiblyAttending(ModelEvent $event): TypedTableSelection {
        return $this->getTable()->where('status', ['participated', 'approved', 'spare', 'applied'])->where('event_id', $event->event_id);
    }

    /**
     * @param ModelEvent $event
     * @return ModelFyziklaniTeam[]
     */
    public function getTeamsAsArray(ModelEvent $event): array {
        $teams = [];
        foreach ($this->findPossiblyAttending($event) as $row) {
            $team = ModelFyziklaniTeam::createFromActiveRow($row);
            $teams[] = $team->__toArray(true);
        }
        return $teams;
    }

    public function isCategoryReadyForClosing(ModelEvent $event, string $category = null): bool {
        $query = $this->findParticipating($event);
        if ($category) {
            $query->where('category', $category);
        }
        $query->where('points', null);
        $count = $query->count();
        return $count == 0;
    }

}
