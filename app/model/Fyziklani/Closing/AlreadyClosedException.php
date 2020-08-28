<?php

namespace FKSDB\Fyziklani\Closing;

use Exception;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Application\BadRequestException;
use Nette\Http\IResponse;

/**
 * Class ClosedSubmittingException
 * @author Michal Červeňák <miso@fykos.cz>
 */
class AlreadyClosedException extends BadRequestException {
    /**
     * ClosedSubmittingException constructor.
     * @param ModelFyziklaniTeam $team
     * @param Exception|NULL $previous
     */
    public function __construct(ModelFyziklaniTeam $team, Exception $previous = NULL) {
        parent::__construct(sprintf(_('Team %s has already closed submitting.'), $team->name), IResponse::S400_BAD_REQUEST, $previous);
    }
}
