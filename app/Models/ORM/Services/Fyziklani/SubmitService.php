<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services\Fyziklani;

use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\EventModel;
use Fykosak\NetteORM\Service;
use Fykosak\NetteORM\TypedSelection;

/**
 * @method SubmitModel createNewModel(array $data)
 */
class SubmitService extends Service
{

    public function findByTaskAndTeam(TaskModel $task, TeamModel2 $team): ?SubmitModel
    {
        $row = $team->getAllSubmits()->where('fyziklani_task_id', $task->fyziklani_task_id)->fetch();
        return $row ? SubmitModel::createFromActiveRow($row) : null;
    }

    public function findAll(EventModel $event): TypedSelection
    {
        return $this->getTable()->where('fyziklani_team.event_id', $event->event_id);
    }

    public function serialiseSubmits(EventModel $event, ?string $lastUpdated): array
    {
        // TODO to related
        $query = $this->findAll($event);
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
