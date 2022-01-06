<?php

declare(strict_types=1);

namespace FKSDB\Models\Fyziklani\Submit;

use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Application\BadRequestException;
use Nette\Http\IResponse;

class ClosedSubmittingException extends BadRequestException {

    public function __construct(ModelFyziklaniTeam $team, ?\Throwable $previous = null) {
        parent::__construct(sprintf(_('Team %s has closed submitting.'), $team->name), IResponse::S400_BAD_REQUEST, $previous);
    }
}
