<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services\Fyziklani;

use FKSDB\Components\Game\Submits\AlreadyRevokedSubmitException;
use FKSDB\Components\Game\Submits\ClosedSubmittingException;
use FKSDB\Components\Game\Submits\PointsMismatchException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitState;
use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\NetteORM\Service\Service;

/**
 * @phpstan-extends Service<SubmitModel>
 * @phpstan-import-type SerializedSubmitModel from SubmitModel
 */
final class SubmitService extends Service
{
    /**
     * @phpstan-return array<int,SerializedSubmitModel>
     */
    public function serialiseSubmits(EventModel $event, ?string $lastUpdated): array
    {
        $query = $this->getTable()->where('fyziklani_task.event_id', $event->event_id);
        $submits = [];
        if ($lastUpdated) {
            $query->where('modified >= ?', $lastUpdated);
        }
        /** @var SubmitModel $submit */
        foreach ($query as $submit) {
            $submits[$submit->fyziklani_submit_id] = $submit->__toArray();
        }
        return $submits;
    }

    public function create(
        TaskModel $task,
        TeamModel2 $team,
        int $points,
        SubmitState $newState
    ): SubmitModel {
        return $this->storeModel([
            'points' => $points,
            'fyziklani_task_id' => $task->fyziklani_task_id,
            'fyziklani_team_id' => $team->fyziklani_team_id,
            'state' => $newState->value,
            'created' => new \DateTimeImmutable(),
        ]);
    }

    /**
     * @throws AlreadyRevokedSubmitException
     * @throws ClosedSubmittingException
     */
    final public function revoke(SubmitModel $submit): void
    {
        $submit->canRevoke();
        $this->storeModel([
            'points' => null,
            'state' => SubmitState::NotChecked,
            'modified' => new \DateTimeImmutable(),
        ], $submit);
    }

    /**
     * @throws ClosedSubmittingException
     * @throws PointsMismatchException
     * @throws \PDOException
     */
    public function check(SubmitModel $submit, int $points): void
    {
        if ($submit->points != $points) {
            throw new PointsMismatchException();
        }
        $this->storeModel([
            'state' => SubmitState::Checked,
            'checked' => new \DateTimeImmutable(),
        ], $submit);
    }

    /**
     * @throws ClosedSubmittingException
     * @throws \PDOException
     */
    public function edit(SubmitModel $submit, int $points): void
    {
        $this->storeModel([
            'points' => $points,
            'state' => SubmitState::Checked,
            'modified' => new \DateTimeImmutable(),
        ], $submit);
    }
}
