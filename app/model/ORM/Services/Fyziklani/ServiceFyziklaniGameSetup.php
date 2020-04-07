<?php

namespace FKSDB\ORM\Services\Fyziklani;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniGameSetup;

/**
 * Class ServiceFyziklaniGameSetup
 * @package FKSDB\ORM\Services\Fyziklani
 */
class ServiceFyziklaniGameSetup extends AbstractServiceSingle {
    /**
     * @return string
     */
    public function getModelClassName(): string {
        return ModelFyziklaniGameSetup::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_FYZIKLANI_GAME_SETUP;
    }
}
