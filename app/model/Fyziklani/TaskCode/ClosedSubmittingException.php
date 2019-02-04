<?php

namespace FKSDB\model\Fyziklani;

use Nette\Application\BadRequestException;
use ORM\Models\Events\ModelFyziklaniTeam;

class ClosedSubmittingException extends BadRequestException {
    public function __construct(ModelFyziklaniTeam $team, int $code = 0, \Exception $previous = NULL) {
        parent::__construct(sprintf(_('Team %s has already closed submitting,'), $team->name), $code, $previous);
    }
}
