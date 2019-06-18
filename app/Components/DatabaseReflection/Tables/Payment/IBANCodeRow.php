<?php

namespace FKSDB\Components\DatabaseReflection\Payment;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class IBANCodeRow
 * @package FKSDB\Components\DatabaseReflection\Payment
 */
class IBANCodeRow extends AbstractPaymentRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('IBAN');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'iban';
    }
}
