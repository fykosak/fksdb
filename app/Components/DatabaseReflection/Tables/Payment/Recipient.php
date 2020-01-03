<?php

namespace FKSDB\Components\DatabaseReflection\Payment;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class BankAccountRow
 * @package FKSDB\Components\DatabaseReflection\Payment
 */
class Recipient extends AbstractPaymentRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('To');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'recipient';
    }
}
