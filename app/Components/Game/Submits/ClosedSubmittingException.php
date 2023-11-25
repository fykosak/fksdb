<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Submits;

use FKSDB\Components\Game\GameException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Nette\Http\IResponse;

class ClosedSubmittingException extends GameException
{
    public function __construct(TeamModel2 $team, ?\Throwable $previous = null)
    {
        parent::__construct(
            sprintf(_('Team %s has closed submitting.'), $team->name),
            IResponse::S400_BAD_REQUEST,
            $previous
        );
    }
}
