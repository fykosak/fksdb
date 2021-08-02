<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Payment;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\ModelPayment;
use Fykosak\NetteORM\AbstractModel;
use Nette\Utils\Html;

class UIdColumnFactory extends ColumnFactory
{

    /**
     * @param AbstractModel|ModelPayment $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModel $model): Html
    {
        return Html::el('span')->addText('#' . $model->getPaymentId());
    }
}
