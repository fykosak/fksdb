<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani;

use Nette\InvalidStateException;

class OldMemberException extends InvalidStateException
{
    public function __construct(\Throwable $previous = null)
    {
        parent::__construct(_('Found old member'), 0, $previous);
    }
}
