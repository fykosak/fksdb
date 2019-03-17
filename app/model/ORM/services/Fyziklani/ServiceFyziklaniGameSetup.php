<?php

namespace FKSDB\ORM\Services\Fyziklani;

use FKSDB\ORM\DbNames;

/**
 * Class ServiceFyziklaniGameSetup
 * @package FKSDB\ORM\Services\Fyziklani
 */
class ServiceFyziklaniGameSetup extends \AbstractServiceSingle {
    protected $tableName = DbNames::TAB_FYZIKLANI_GAME_SETUP;
    protected $modelClassName = 'FKSDB\ORM\Models\Fyziklani\ModelFyziklaniGameSetup';
}
