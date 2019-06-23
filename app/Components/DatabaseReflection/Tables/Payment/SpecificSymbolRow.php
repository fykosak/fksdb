<?php

namespace FKSDB\Components\DatabaseReflection\Payment;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class SpecificSymbolRow
 * @package FKSDB\Components\DatabaseReflection\Payment
 */
class SpecificSymbolRow  extends AbstractPaymentRow {
    use DefaultPrinterTrait;
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Specific symbol');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'specific_symbol';
    }
}
