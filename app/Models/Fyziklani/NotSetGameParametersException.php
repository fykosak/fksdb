<?php

declare(strict_types=1);

namespace FKSDB\Models\Fyziklani;

use FKSDB\Models\Exceptions\NotImplementedException;

class NotSetGameParametersException extends NotImplementedException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct(_('Game parameters not set.'), $previous);
    }
}
