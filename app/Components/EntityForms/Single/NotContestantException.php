<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Single;

use Nette\InvalidStateException;

class NotContestantException extends InvalidStateException
{
    /**
     * @param 
     */
    public function __construct()
    {
        parent::__construct(_('Person must be an active contestant.'));
    }
    
}
