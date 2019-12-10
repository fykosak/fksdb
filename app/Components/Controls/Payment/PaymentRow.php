<?php

namespace FKSDB\Components\Controls\Payment;

use FKSDB\ORM\Models\ModelPayment;
use Nette\Utils\Html;

/**
 * Class PaymentRow
 * @package FKSDB\Components\Controls\Payment
 */
class PaymentRow {
    /**
     * @param ModelPayment|null $modelPayment
     * @return Html
     */
    public static function getHtml(ModelPayment $modelPayment = null): Html {
        if (!$modelPayment) {
            return Html::el('span')->addAttributes(['class' => 'badge badge-danger'])->addText('No payment found');
        }
        return Html::el('span')->addAttributes(['class' => $modelPayment->getUIClass()])->addText('#' . $modelPayment->getPaymentId() . '-' . $modelPayment->getStateLabel());

    }
}