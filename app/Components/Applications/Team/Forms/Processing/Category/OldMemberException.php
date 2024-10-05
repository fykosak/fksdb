<?php

declare(strict_types=1);

namespace FKSDB\Components\Applications\Team\Forms\Processing\Category;

use FKSDB\Components\EntityForms\Processing\ProcessingException;

class OldMemberException extends ProcessingException
{
    public function __construct(\Throwable $previous = null)
    {
        parent::__construct(_('Found old member'), 0, $previous);
    }
}
