<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use Nette\Application\BadRequestException;

class NoContestYearAvailable extends BadRequestException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct(_('No year available'), 404, $previous);
    }
}
