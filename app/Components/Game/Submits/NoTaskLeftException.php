<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Submits;

use FKSDB\Components\Game\GameException;

class NoTaskLeftException extends GameException
{
    public function __construct()
    {
        parent::__construct(_('No task left on board'));
    }
}
