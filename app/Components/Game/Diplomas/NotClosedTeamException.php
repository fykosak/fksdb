<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Diplomas;

use FKSDB\Components\Game\GameException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Nette\Http\IResponse;

class NotClosedTeamException extends GameException
{

    public function __construct(TeamModel2 $team, ?\Throwable $previous = null)
    {
        parent::__construct(
            sprintf(_('Team %s (%d) has not closed submitting'), $team->name, $team->fyziklani_team_id),
            IResponse::S400_BadRequest,
            $previous
        );
    }
}
