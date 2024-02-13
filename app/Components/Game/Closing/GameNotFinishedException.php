<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Closing;

use FKSDB\Components\Game\GameException;
use Nette\Http\IResponse;

final class GameNotFinishedException extends GameException
{

    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct(
            sprintf(_('Game is not finished.')),
            IResponse::S400_BAD_REQUEST,
            $previous
        );
    }
}
