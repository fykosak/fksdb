<?php

namespace FKSDB\Components\DatabaseReflection\Payment;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPayment;
use Nette\Utils\Html;

/**
 * Class BankAccountRow
 * @package FKSDB\Components\DatabaseReflection\Payment
 */
class IdRow extends AbstractPaymentRow {

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Payment id');
    }

    /**
     * @param ModelPayment|AbstractModelSingle $model
     * @inheritDoc
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return Html::el('span')->addText('#' . $model->getPaymentId());
    }
}
