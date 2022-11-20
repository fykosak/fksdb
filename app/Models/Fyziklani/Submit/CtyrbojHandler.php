<?php

declare(strict_types=1);

namespace FKSDB\Models\Fyziklani\Submit;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitState;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;

class CtyrbojHandler extends Handler
{
    /**
     * @throws PointsMismatchException
     * @throws TaskCodeException
     * @throws ClosedSubmittingException
     */
    protected function savePoints(Logger $logger, TeamModel2 $team, TaskModel $task, ?int $points): void
    {
        $submit = $this->submitService->findByTaskAndTeam($task, $team);
        if (is_null($submit)) { // novo zadaný
            $this->create($logger, $task, $team, null);
        } elseif (is_null($submit->points)) { // ak bol zmazaný
            $this->edit($logger, $submit, null);
        } else {
            throw new TaskCodeException(_('Task was given.'));
        }
    }

    /**
     * @throws ClosedSubmittingException
     * @throws ModelException
     */
    public function edit(Logger $logger, SubmitModel $submit, ?int $points): void
    {
        if (!$submit->fyziklani_team->hasOpenSubmitting()) {
            throw new ClosedSubmittingException($submit->fyziklani_team);
        }
        $this->submitService->storeModel([
            'points' => $submit->fyziklani_task->points,
            'state' => SubmitState::CHECKED,
            'modified' => new \DateTimeImmutable(),
        ], $submit);
        $this->logEvent($submit, 'edited', \sprintf(' points %d', $submit->fyziklani_task->points));
        $logger->log(
            new Message(
                \sprintf(
                    _('Points edited. %d points, team: "%s" (%d), task: %s "%s"'),
                    $submit->fyziklani_task->points,
                    $submit->fyziklani_team->name,
                    $submit->fyziklani_team->fyziklani_team_id,
                    $submit->fyziklani_task->label,
                    $submit->fyziklani_task->name
                ),
                Message::LVL_SUCCESS
            )
        );
    }

    /**
     * @throws NotImplementedException
     */
    public function check(Logger $logger, SubmitModel $submit, ?int $points): void
    {
        throw new NotImplementedException();
    }

    public function create(Logger $logger, TaskModel $task, TeamModel2 $team, ?int $points): void
    {
        $submit = $this->submitService->storeModel([
            'points' => $task->points,
            'fyziklani_task_id' => $task->fyziklani_task_id,
            'fyziklani_team_id' => $team->fyziklani_team_id,
            'state' => SubmitState::CHECKED,
        ]);
        $this->logEvent($submit, 'created', \sprintf(' points %d', $task->points));

        $logger->log(
            new Message(
                \sprintf(
                    _('Points saved; %d points, team: "%s" (%d), task: %s "%s"'),
                    $task->points,
                    $team->name,
                    $team->fyziklani_team_id,
                    $task->label,
                    $task->name
                ),
                Message::LVL_SUCCESS
            )
        );
    }
}
