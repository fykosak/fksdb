<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Submits;

use FKSDB\Components\Game\GameException;
use Nette\Http\IResponse;

final class OutOfGameTimeException extends GameException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct(
            _('It\'s too early before or too late after the game.'),
            IResponse::S400_BAD_REQUEST,
            $previous
        );
    }
}
