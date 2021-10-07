<?php

declare(strict_types=1);

namespace FKSDB\Models\Payment\SymbolGenerator;

use Nette\InvalidStateException;

class AlreadyGeneratedSymbolsException extends InvalidStateException
{
}
