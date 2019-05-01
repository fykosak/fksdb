<?php

namespace FKSDB\Components\DatabaseReflection\Payment;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class ConstantSymbolRow
 * @package FKSDB\Components\DatabaseReflection\Payment
 */
class ConstantSymbolRow extends AbstractPaymentRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Constant symbol');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'constant_symbol';
    }
}
