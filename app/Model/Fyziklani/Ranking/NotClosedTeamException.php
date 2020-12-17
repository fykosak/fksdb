<?php

namespace FKSDB\Model\Fyziklani\Ranking;

use FKSDB\Model\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Application\BadRequestException;
use Nette\Http\IResponse;

/**
 * Class NotClosedTeamException
 * @author Michal Červeňák <miso@fykos.cz>
 */
class NotClosedTeamException extends BadRequestException {

    public function __construct(ModelFyziklaniTeam $team, ?\Throwable $previous = null) {
        parent::__construct(sprintf(_('Team %s (%d) has not closed submitting'), $team->name, $team->e_fyziklani_team_id), IResponse::S400_BAD_REQUEST, $previous);
    }
}
