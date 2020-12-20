<?php

namespace FKSDB\Models\ORM\Columns\Tables\Payment;

use FKSDB\Models\ORM\Columns\Types\DefaultColumnFactory;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\ModelPayment;
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
