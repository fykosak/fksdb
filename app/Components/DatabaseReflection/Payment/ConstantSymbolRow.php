<?php

namespace FKSDB\Components\DatabaseReflection\Payment;
/**
 * Class ConstantSymbolRow
 * @package FKSDB\Components\DatabaseReflection\Payment
 */
class ConstantSymbolRow extends AbstractPaymentRow {
    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Constant symbol');
    }
}
