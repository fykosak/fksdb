<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\EventRole;

use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;

class FyziklaniTeacherRole implements EventRole
{
    /** @var ModelFyziklaniTeam[] */
    public array $teams;

    public function __construct(array $teams)
    {
        $this->teams = $teams;
    }
}
