<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani;

use Nette\InvalidStateException;

class DuplicateTeamNameException extends InvalidStateException
{
    public function __construct(string $teamName, \Throwable $previous = null)
    {
        parent::__construct(sprintf(_('Team with name "%s" already exists.'), $teamName), 0, $previous);
    }
}
