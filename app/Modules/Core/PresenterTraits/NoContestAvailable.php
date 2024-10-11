<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use Nette\Application\BadRequestException;

class NoContestAvailable extends BadRequestException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct(_('No contest available'), 404, $previous);
    }
}
