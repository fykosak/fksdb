<?php

namespace FKSDB\Models\ORM\Columns\Tables\Payment;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\ModelPayment;
use Nette\Utils\Html;

/**
 * Class IdRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class UIdColumnFactory extends ColumnFactory {

    /**
     * @param AbstractModel|ModelPayment $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModel $model): Html {
        return Html::el('span')->addText('#' . $model->getPaymentId());
    }
}
