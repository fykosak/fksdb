<?php

namespace FKSDB\Fyziklani\Closing;

use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Application\BadRequestException;
use Nette\Http\Response;

/**
 * Class NotCheckedSubmitsException
 * @author Michal Červeňák <miso@fykos.cz>
 */
class NotCheckedSubmitsException extends BadRequestException {
    /**
     * NotCheckedSubmitsException constructor.
     * @param ModelFyziklaniTeam $team
     * @param \Exception|NULL $previous
     */
    public function __construct(ModelFyziklaniTeam $team, \Exception $previous = null) {
        parent::__construct(sprintf(_('Team %s has non checked submits'), $team->name), Response::S400_BAD_REQUEST, $previous);
    }
}
