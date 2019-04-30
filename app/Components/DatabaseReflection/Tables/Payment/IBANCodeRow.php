<?php

namespace FKSDB\Components\DatabaseReflection\Payment;

/**
 * Class IBANCodeRow
 * @package FKSDB\Components\DatabaseReflection\Payment
 */
class IBANCodeRow extends AbstractPaymentRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('IBAN');
    }
}
