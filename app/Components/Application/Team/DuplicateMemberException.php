<?php

declare(strict_types=1);

namespace FKSDB\Components\Application\Team;

use FKSDB\Models\ORM\Models\PersonModel;
use Nette\InvalidStateException;

class DuplicateMemberException extends InvalidStateException
{
    public function __construct(PersonModel $person, \Throwable $previous = null)
    {
        parent::__construct(sprintf(_('Person "%s" already applied.'), $person->getFullName()), 0, $previous);
    }
}
