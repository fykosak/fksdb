<?php

namespace FKSDB\Components\DatabaseReflection\Payment;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class SWIFTCodeRow
 * @package FKSDB\Components\DatabaseReflection\Payment
 */
class SWIFTCodeRow extends AbstractPaymentRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('SWIFT');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'swift';
    }
}
