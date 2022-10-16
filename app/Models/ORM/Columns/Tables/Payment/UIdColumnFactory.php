<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Payment;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\PaymentModel;
use Nette\Utils\Html;

class UIdColumnFactory extends ColumnFactory
{
    /**
     * @param PaymentModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        return Html::el('span')->addText('#' . $model->getPaymentId());
    }
}
