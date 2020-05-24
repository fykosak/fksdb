<?php

namespace FKSDB\Fyziklani;

use Exception;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Application\BadRequestException;

/**
 * Class ClosedSubmittingException
 * *
 */
class ClosedSubmittingException extends BadRequestException {
    /**
     * ClosedSubmittingException constructor.
     * @param ModelFyziklaniTeam $team
     * @param int $code
     * @param Exception|NULL $previous
     */
    public function __construct(ModelFyziklaniTeam $team, int $code = 0, Exception $previous = NULL) {
        parent::__construct(sprintf(_('Team %s has already closed submitting.'), $team->name), $code, $previous);
    }
}
