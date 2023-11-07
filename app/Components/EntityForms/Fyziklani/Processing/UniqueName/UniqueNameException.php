<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani\Processing\UniqueName;

use Nette\InvalidStateException;

class UniqueNameException extends InvalidStateException
{
    public function __construct(string $teamName, \Throwable $previous = null)
    {
        parent::__construct(sprintf(_('Team with name "%s" already exists.'), $teamName), 0, $previous);
    }
}
