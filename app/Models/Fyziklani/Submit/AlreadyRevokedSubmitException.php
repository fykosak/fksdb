<?php

declare(strict_types=1);

namespace FKSDB\Models\Fyziklani\Submit;

use Nette\Application\BadRequestException;
use Nette\Http\IResponse;

class AlreadyRevokedSubmitException extends BadRequestException
{

    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct(_('Submit is already revoked'), IResponse::S400_BAD_REQUEST, $previous);
    }
}
