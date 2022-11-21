<?php

declare(strict_types=1);

namespace FKSDB\Models\Fyziklani\Submit;

use FKSDB\Models\Fyziklani\FyziklaniException;
use Nette\Http\IResponse;

class AlreadyRevokedSubmitException extends FyziklaniException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct(_('Submit is already revoked'), IResponse::S400_BAD_REQUEST, $previous);
    }
}
