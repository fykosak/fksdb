<?php

declare(strict_types=1);

namespace FKSDB\Models\Fyziklani\Submit;

use FKSDB\Models\ORM\Models\Fyziklani\SubmitState;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;

class FOFHandler extends Handler
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
            $this->create($logger, $task, $team, $points);
        } elseif (is_null($submit->points)) { // ak bol zmazaný
            $this->edit($logger, $submit, $points);
        } elseif (!$submit->isChecked()) { // check bodovania
            $this->check($logger, $submit, $points);
        } else {
            throw new TaskCodeException(_('Task given and validated.'));
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
            'points' => $points,
            'state' => SubmitState::CHECKED,
            'modified' => new \DateTimeImmutable(),
        ], $submit);
        $this->logEvent($submit, 'edited', \sprintf(' points %d', $points));
        $logger->log(
            new Message(
                \sprintf(
                    _('Points edited. %d points, team: "%s" (%d), task: %s "%s"'),
                    $points,
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
     * @throws ClosedSubmittingException
     * @throws PointsMismatchException
     * @throws ModelException
     */
    public function check(Logger $logger, SubmitModel $submit, ?int $points): void
    {
        if (!$submit->fyziklani_team->hasOpenSubmitting()) {
            throw new ClosedSubmittingException($submit->fyziklani_team);
        }
        if ($submit->points != $points) {
            throw new PointsMismatchException();
        }
        $this->submitService->storeModel([
            'state' => SubmitState::CHECKED,
        ], $submit);
        $this->logEvent($submit, 'checked');

        $logger->log(
            new Message(
                \sprintf(
                    _('Scoring has been checked. %d points, team "%s" (%d), task %s "%s".'),
                    $points,
                    $submit->fyziklani_team->name,
                    $submit->fyziklani_team->fyziklani_team_id,
                    $submit->fyziklani_task->label,
                    $submit->fyziklani_task->name
                ),
                Message::LVL_SUCCESS
            )
        );
    }

    public function create(Logger $logger, TaskModel $task, TeamModel2 $team, ?int $points): void
    {
        $submit = $this->submitService->storeModel([
            'points' => $points,
            'fyziklani_task_id' => $task->fyziklani_task_id,
            'fyziklani_team_id' => $team->fyziklani_team_id,
            'state' => SubmitState::NOT_CHECKED,
        ]);
        $this->logEvent($submit, 'created', \sprintf(' points %d', $points));

        $logger->log(
            new Message(
                \sprintf(
                    _('Points saved; %d points, team: "%s" (%d), task: %s "%s"'),
                    $points,
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
