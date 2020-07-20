<?php

namespace FKSDB\Fyziklani\Ranking;

use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Application\BadRequestException;
use Nette\Http\IResponse;

/**
 * Class NotClosedTeamException
 * @author Michal Červeňák <miso@fykos.cz>
 */
class NotClosedTeamException extends BadRequestException {
    /**
     * NotClosedTeamException constructor.
     * @param ModelFyziklaniTeam $team
     * @param \Exception|null $previous
     */
    public function __construct(ModelFyziklaniTeam $team, \Exception $previous = null) {
        parent::__construct(sprintf(_('Team %s (%d) has not closed submitting'), $team->name, $team->e_fyziklani_team_id), IResponse::S400_BAD_REQUEST, $previous);
    }
}
