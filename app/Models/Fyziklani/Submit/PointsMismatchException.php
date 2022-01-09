<?php

declare(strict_types=1);

namespace FKSDB\Models\Fyziklani\Submit;

class PointsMismatchException extends TaskCodeException
{
    public function __construct(int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(_('Points mismatch'), $code, $previous);
    }
}
