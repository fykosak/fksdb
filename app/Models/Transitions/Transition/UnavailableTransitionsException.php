<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition;

use Nette\InvalidStateException;

class UnavailableTransitionsException extends InvalidStateException
{
}
