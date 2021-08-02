<?php

namespace FKSDB\Models\Fyziklani\Submit;

class ControlMismatchException extends TaskCodeException
{

    public function __construct(int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(_('Wrong task number.'), $code, $previous);
    }
}
