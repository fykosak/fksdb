<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\Components\DatabaseReflection\FieldLevelPermission;
use Nette\Forms\Controls\BaseControl;
use FKSDB\Exceptions\NotImplementedException;

/**
 * Class AbstractParticipantRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractParticipantRow extends AbstractColumnFactory {

    /**
     * @param array $args
     * @return BaseControl
     * @throws NotImplementedException
     */
    public function createField(...$args): BaseControl {
        throw new NotImplementedException();
    }

    public function getPermission(): FieldLevelPermission {
        return new FieldLevelPermission(self::PERMISSION_ALLOW_ANYBODY, self::PERMISSION_ALLOW_ANYBODY);
    }
}
