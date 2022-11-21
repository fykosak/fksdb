<?php

declare(strict_types=1);

namespace FKSDB\Models\Fyziklani;

class NotSetGameParametersException extends FyziklaniException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct(_('Game parameters not set.'), $previous);
    }
}
