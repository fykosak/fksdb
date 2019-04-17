<?php

namespace FKSDB\Components\DatabaseReflection\Payment;

/**
 * Class BankNameRow
 * @package FKSDB\Components\DatabaseReflection\Payment
 */
class BankNameRow extends AbstractPaymentRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Bank name');
    }
}
