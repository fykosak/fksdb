<?php

namespace FKSDB\Model\Fyziklani\Closing;

use FKSDB\Model\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Application\BadRequestException;
use Nette\Http\IResponse;

/**
 * Class ClosedSubmittingException
 * @author Michal Červeňák <miso@fykos.cz>
 */
class AlreadyClosedException extends BadRequestException {

    public function __construct(ModelFyziklaniTeam $team, ?\Throwable $previous = null) {
        parent::__construct(sprintf(_('Team %s has already closed submitting.'), $team->name), IResponse::S400_BAD_REQUEST, $previous);
    }
}
