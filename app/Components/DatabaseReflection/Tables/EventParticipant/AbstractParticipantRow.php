<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use Nette\Forms\IControl;
use Nette\NotImplementedException;

/**
 * Class AbstractParticipantRow
 * @package FKSDB\Components\DatabaseReflection\PersonInfo
 */
abstract class AbstractParticipantRow extends AbstractRow {

    /**
     * @return IControl
     */
    public function creteField(): IControl {
        throw new NotImplementedException();
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }
}
