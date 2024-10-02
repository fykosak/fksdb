<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Submits\Handler;

use FKSDB\Components\Game\GameException;
use FKSDB\Components\Game\Submits\ClosedSubmittingException;
use FKSDB\Components\Game\Submits\PointsMismatchException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitState;
use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Nette\Application\UI\InvalidLinkException;

class CtyrbojHandler extends Handler
{
    /**
     * @throws PointsMismatchException
     * @throws ClosedSubmittingException
     * @throws InvalidLinkException
     */
    public function handle(TeamModel2 $team, TaskModel $task, ?int $points): void
    {
        $submit = $team->getSubmit($task);
        if (is_null($submit)) { // novo zadaný
            $this->create($task, $team, $task->points, SubmitState::from(SubmitState::Checked));
        } elseif (is_null($submit->points)) { // ak bol zmazaný
            $this->edit($submit, $task->points);
        } else {
            throw new GameException(\sprintf(_('Task was already submitted'), $submit->points));
        }
    }

    /**
     * @throws NotImplementedException
     */
    public function check(SubmitModel $submit, ?int $points): void
    {
        throw new NotImplementedException();
    }

    public function logPriority(): string
    {
        return 'ctyrboj-info';
    }
}
