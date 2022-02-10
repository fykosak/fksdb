<?php

declare(strict_types=1);

namespace FKSDB\Models\Fyziklani\Closing;

use FKSDB\Models\ORM\Models\Fyziklani\TeamModel;
use Nette\Application\BadRequestException;
use Nette\Http\IResponse;

class AlreadyClosedException extends BadRequestException
{

    public function __construct(TeamModel $team, ?\Throwable $previous = null)
    {
        parent::__construct(
            sprintf(_('Team %s has already closed submitting.'), $team->name),
            IResponse::S400_BAD_REQUEST,
            $previous
        );
    }
}
