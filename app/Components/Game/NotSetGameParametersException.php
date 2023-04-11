<?php

declare(strict_types=1);

namespace FKSDB\Components\Game;

class NotSetGameParametersException extends GameException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct(_('Game parameters not set.'), 0, $previous);
    }
}
