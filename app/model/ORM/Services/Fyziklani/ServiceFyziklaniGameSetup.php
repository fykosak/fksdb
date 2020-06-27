<?php

namespace FKSDB\ORM\Services\Fyziklani;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\DeprecatedLazyDBTrait;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniGameSetup;

/**
 * Class ServiceFyziklaniGameSetup
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServiceFyziklaniGameSetup extends AbstractServiceSingle {
    use DeprecatedLazyDBTrait;
    public function getModelClassName(): string {
        return ModelFyziklaniGameSetup::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_FYZIKLANI_GAME_SETUP;
    }
}
