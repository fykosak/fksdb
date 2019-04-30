<?php

namespace FKSDB\Components\DatabaseReflection\Payment;

/**
 * Class SpecificSymbolRow
 * @package FKSDB\Components\DatabaseReflection\Payment
 */
class SpecificSymbolRow  extends AbstractPaymentRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Specific symbol');
    }
}
