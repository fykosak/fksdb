<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services\Fyziklani;

use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel;
use FKSDB\Models\ORM\Models\ModelEvent;
use Fykosak\NetteORM\AbstractService;
use Fykosak\NetteORM\TypedTableSelection;

/**
 * @method SubmitModel createNewModel(array $data)
 */
class SubmitService extends AbstractService
{

    public function findByTaskAndTeam(TaskModel $task, TeamModel $team): ?SubmitModel
    {
        $row = $team->getAllSubmits()->where('fyziklani_task_id', $task->fyziklani_task_id)->fetch();
        return $row ? SubmitModel::createFromActiveRow($row) : null;
    }

    public function findAll(ModelEvent $event): TypedTableSelection
    {
        return $this->getTable()->where('e_fyziklani_team_id.event_id', $event->event_id);
    }

    public function serialiseSubmits(ModelEvent $event, ?string $lastUpdated): array
    {
        // TODO to related
        $query = $this->getTable()->where('e_fyziklani_team.event_id', $event->event_id);
        $submits = [];
        if ($lastUpdated) {
            $query->where('modified >= ?', $lastUpdated);
        }
        foreach ($query as $row) {
            $submit = SubmitModel::createFromActiveRow($row);
            $submits[$submit->fyziklani_submit_id] = $submit->__toArray();
        }
        return $submits;
    }
}
