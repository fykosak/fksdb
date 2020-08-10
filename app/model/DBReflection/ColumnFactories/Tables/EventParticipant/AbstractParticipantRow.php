<?php

namespace FKSDB\DBReflection\ColumnFactories\EventParticipant;

use FKSDB\DBReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\DBReflection\FieldLevelPermission;
use FKSDB\DBReflection\OmittedControlException;
use Nette\Forms\Controls\BaseControl;

/**
 * Class AbstractParticipantRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractParticipantRow extends AbstractColumnFactory {

    public function createField(...$args): BaseControl {
        throw new OmittedControlException();
    }

    public function getPermission(): FieldLevelPermission {
        return new FieldLevelPermission(self::PERMISSION_ALLOW_ANYBODY, self::PERMISSION_ALLOW_ANYBODY);
    }
}
