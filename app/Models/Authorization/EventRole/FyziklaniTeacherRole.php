<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\EventRole;

use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\ModelEvent;

class FyziklaniTeacherRole extends EventRole
{
    /** @var TeamModel2[] */
    public array $teams;

    public function __construct(ModelEvent $event, array $teams)
    {
        parent::__construct('event.fyziklaniTeacher', $event);
        $this->teams = $teams;
    }
}
