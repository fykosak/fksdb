<?php

namespace FKSDB\DBReflection\ColumnFactories\Tables\Payment;

use FKSDB\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\ORM\Models\AbstractModelSingle;
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
