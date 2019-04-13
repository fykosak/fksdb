<?php

namespace FKSDB\Components\DatabaseReflection\Payment;

/**
 * Class SWIFTCodeRow
 * @package FKSDB\Components\DatabaseReflection\Payment
 */
class SWIFTCodeRow extends AbstractPaymentRow {
    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('SWIFT');
    }
}
