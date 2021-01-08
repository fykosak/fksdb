<?php

namespace FKSDB\Models\Fyziklani\Closing;

use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Application\BadRequestException;
use Nette\Http\Response;

/**
 * Class NotCheckedSubmitsException
 * @author Michal Červeňák <miso@fykos.cz>
 */
class NotCheckedSubmitsException extends BadRequestException {

    public function __construct(ModelFyziklaniTeam $team, ?\Throwable $previous = null) {
        parent::__construct(sprintf(_('Team %s has non checked submits'), $team->name), Response::S400_BAD_REQUEST, $previous);
    }
}
