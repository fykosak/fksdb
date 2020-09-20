<?php

namespace FKSDB\DBReflection\ColumnFactories\Payment;

use FKSDB\DBReflection\ColumnFactories\DefaultColumnFactory;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPayment;
use Nette\Utils\Html;

/**
 * Class IdRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class UIdRow extends DefaultColumnFactory {

    /**
     * @param AbstractModelSingle|ModelPayment $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return Html::el('span')->addText('#' . $model->getPaymentId());
    }
}
