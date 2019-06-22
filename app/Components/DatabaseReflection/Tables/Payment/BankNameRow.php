<?php

namespace FKSDB\Components\DatabaseReflection\Payment;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class BankNameRow
 * @package FKSDB\Components\DatabaseReflection\Payment
 */
class BankNameRow extends AbstractPaymentRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Bank name');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'bank_name';
    }
}
