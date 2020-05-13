<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniGameSetup;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use Nette\Forms\Controls\BaseControl;
use FKSDB\Exceptions\NotImplementedException;

/**
 * Class AbstractFyziklaniGameSetupRow
 * @package FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniGameSetup
 */
abstract class AbstractFyziklaniGameSetupRow extends AbstractRow {
    /**
     * @return int
     */
    final public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

    /**
     * @param array $args
     * @return BaseControl
     * @throws NotImplementedException
     */
    public function createField(...$args): BaseControl {
        throw new NotImplementedException;
    }
}
