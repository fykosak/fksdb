<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Submits;

use FKSDB\Components\Game\GameException;
use Nette\Http\IResponse;

class AlreadyRevokedSubmitException extends GameException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct(_('Submit is already revoked'), IResponse::S400_BadRequest, $previous);
    }
}
