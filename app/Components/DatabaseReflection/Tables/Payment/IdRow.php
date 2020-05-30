<?php

namespace FKSDB\Components\DatabaseReflection\Payment;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPayment;
use Nette\Utils\Html;

/**
 * Class BankAccountRow
 * *
 */
class IdRow extends AbstractPaymentRow {

    public function getTitle(): string {
        return _('Payment id');
    }

    /**
     * @param AbstractModelSingle|ModelPayment $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return Html::el('span')->addText('#' . $model->getPaymentId());
    }
}
