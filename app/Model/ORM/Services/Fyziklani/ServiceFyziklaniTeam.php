<?php

namespace FKSDB\Model\ORM\Services\Fyziklani;

use FKSDB\Model\ORM\DbNames;
use FKSDB\Model\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Model\ORM\Models\ModelEvent;
use FKSDB\Model\ORM\Services\AbstractServiceSingle;
use FKSDB\Model\ORM\Tables\TypedTableSelection;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Michal Červeňák <miso@fykos.cz>
 * @method ModelFyziklaniTeam|null findByPrimary($key)
 */
class ServiceFyziklaniTeam extends AbstractServiceSingle {

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_E_FYZIKLANI_TEAM, ModelFyziklaniTeam::class);
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
        return $query->count() == 0;
    }
}