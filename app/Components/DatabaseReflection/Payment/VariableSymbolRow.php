<?php

namespace FKSDB\Components\DatabaseReflection\Payment;

/**
 * Class VariableSymbolRow
 * @package FKSDB\Components\DatabaseReflection\Payment
 */
class VariableSymbolRow extends AbstractPaymentRow {
    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Variable symbol');
    }
}
