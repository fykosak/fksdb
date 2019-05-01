<?php

namespace FKSDB\Components\DatabaseReflection\Payment;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class VariableSymbolRow
 * @package FKSDB\Components\DatabaseReflection\Payment
 */
class VariableSymbolRow extends AbstractPaymentRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Variable symbol');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'variable_symbol';
    }
}
