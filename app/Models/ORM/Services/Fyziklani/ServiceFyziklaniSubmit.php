<?php

namespace FKSDB\Models\ORM\Services\Fyziklani;

use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTask;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Models\ORM\Models\ModelEvent;
use Fykosak\NetteORM\AbstractService;
use Fykosak\NetteORM\TypedTableSelection;

/**
 * @method ModelFyziklaniSubmit createNewModel(array $data)
 */
class ServiceFyziklaniSubmit extends AbstractService {

    public function findByTaskAndTeam(ModelFyziklaniTask $task, ModelFyziklaniTeam $team): ?ModelFyziklaniSubmit {
        $row = $team->getAllSubmits()->where('fyziklani_task_id', $task->fyziklani_task_id)->fetch();
        return $row ? ModelFyziklaniSubmit::createFromActiveRow($row) : null;
    }

    public function findAll(ModelEvent $event): TypedTableSelection {
        return $this->getTable()->where('e_fyziklani_team_id.event_id', $event->event_id);
    }

    public function getSubmitsAsArray(ModelEvent $event, ?string $lastUpdated): array {
        // TODO to related
        $query = $this->getTable()->where('e_fyziklani_team.event_id', $event->event_id);
        $submits = [];
        if ($lastUpdated) {
            $query->where('modified >= ?', $lastUpdated);
        }
        foreach ($query as $row) {
            $submit = ModelFyziklaniSubmit::createFromActiveRow($row);
            $submits[$submit->fyziklani_submit_id] = $submit->__toArray();
        }
        return $submits;
    }
}
