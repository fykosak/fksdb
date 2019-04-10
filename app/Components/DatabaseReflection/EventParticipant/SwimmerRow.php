<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\Controls\Helpers\ValuePrinters\BinaryValueControl;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Utils\Html;

/**
 * Class SwimmerRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class SwimmerRow extends AbstractParticipantRow {
    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Swimmer');
    }

    /**
     * @param AbstractModelSingle $model
     * @param string $fieldName
     * @param int $userPermissionsLevel
     * @return Html
     */
    public function createHtmlValue(AbstractModelSingle $model, string $fieldName, int $userPermissionsLevel): Html {
        return BinaryValueControl::renderStatic($model, $fieldName, $this->hasPermissions($userPermissionsLevel));
    }
}
