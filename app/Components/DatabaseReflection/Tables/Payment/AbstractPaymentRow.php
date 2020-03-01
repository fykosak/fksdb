<?php

namespace FKSDB\Components\DatabaseReflection\Payment;

use Nette\Forms\Controls\BaseControl;
use FKSDB\NotImplementedException;

/**
 * Class AbstractPaymentRow
 * @package FKSDB\Components\DatabaseReflection\Payment
 */
abstract class AbstractPaymentRow extends \FKSDB\Components\DatabaseReflection\AbstractRow {
    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        throw new NotImplementedException();
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }
}
