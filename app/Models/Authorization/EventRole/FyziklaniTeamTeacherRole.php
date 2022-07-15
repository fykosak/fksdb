<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\EventRole;

use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\ModelEvent;

class FyziklaniTeamTeacherRole extends EventRole
{
    /** @var TeamModel2[] */
    public array $teams;

    public function __construct(ModelEvent $event, array $teams)
    {
        parent::__construct('event.fyziklaniTeamTeacher', $event);
        $this->teams = $teams;
    }
}
