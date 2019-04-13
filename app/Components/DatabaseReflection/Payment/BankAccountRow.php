<?php

namespace FKSDB\Components\DatabaseReflection\Payment;

/**
 * Class BankAccountRow
 * @package FKSDB\Components\DatabaseReflection\Payment
 */
class BankAccountRow extends AbstractPaymentRow {
    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Bank account');
    }
}
