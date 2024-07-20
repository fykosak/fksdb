<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Submits\Handler;

use FKSDB\Components\Game\GameException;
use FKSDB\Components\Game\Submits\ClosedSubmittingException;
use FKSDB\Components\Game\Submits\PointsMismatchException;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitState;
use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;

class FOFHandler extends Handler
{
    /**
     * @throws PointsMismatchException
     * @throws ClosedSubmittingException
     */
    public function handle(TeamModel2 $team, TaskModel $task, ?int $points): void
    {
        if (is_null($points) || $points === 0) {
            throw new GameException(_('Points can not be a NULL or 0'));
        }
        $submit = $team->getSubmit($task);
        if (is_null($submit)) { // novo zadaný
            $this->create($task, $team, $points, SubmitState::from(SubmitState::NotChecked));
        } elseif (is_null($submit->points)) { // ak bol zmazaný
            $this->edit($submit, $points);
        } elseif ($submit->state->value !== SubmitState::Checked) { // check bodovania
            $this->check($submit, $points);
        } else {
            throw new GameException(\sprintf(_('Task was already submitted and checked'), $submit->points));
        }
    }

    public function logPriority(): string
    {
        return 'fof-info';
    }
}
