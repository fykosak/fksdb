<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Submits;

use FKSDB\Components\Game\GameException;
use Nette\Http\IResponse;

final class GameNotStartedException extends GameException
{

    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct(
            sprintf(_('Game has not started yet.')),
            IResponse::S400_BAD_REQUEST,
            $previous
        );
    }
}
