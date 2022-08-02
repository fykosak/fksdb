<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\EventRole;

use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\EventModel;

class FyziklaniTeamTeacherRole extends EventRole
{
    /** @var TeamModel2[] */
    public array $teams;

    public function __construct(EventModel $event, array $teams)
    {
        parent::__construct('event.fyziklaniTeamTeacher', $event);
        $this->teams = $teams;
    }
}
