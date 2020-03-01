<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniGameSetup;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use Nette\Forms\Controls\BaseControl;
use FKSDB\NotImplementedException;

/**
 * Class AbstractFyziklaniGameSetupRow
 * @package FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniGameSetup
 */
abstract class AbstractFyziklaniGameSetupRow extends AbstractRow {
    /**
     * @return int
     */
    public final function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        throw new NotImplementedException();
    }
}
