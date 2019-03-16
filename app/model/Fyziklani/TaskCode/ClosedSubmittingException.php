<?php

namespace FKSDB\model\Fyziklani;

use FKSDB\ORM\Models\Events\ModelFyziklaniTeam;
use Nette\Application\BadRequestException;

/**
 * Class ClosedSubmittingException
 * @package FKSDB\model\Fyziklani
 */
class ClosedSubmittingException extends BadRequestException {
    /**
     * ClosedSubmittingException constructor.
     * @param ModelFyziklaniTeam $team
     * @param int $code
     * @param \Exception|NULL $previous
     */
    public function __construct(ModelFyziklaniTeam $team, int $code = 0, \Exception $previous = NULL) {
        parent::__construct(sprintf(_('Team %s has already closed submitting.'), $team->name), $code, $previous);
    }
}
