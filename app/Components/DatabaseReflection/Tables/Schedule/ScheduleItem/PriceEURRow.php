<?php

namespace FKSDB\Components\DatabaseReflection\Tables\Schedule\ScheduleItem;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\Payment\Price;
use FKSDB\Payment\PriceCalculator\UnsupportedCurrencyException;
use Nette\Utils\Html;

/**
 * Class PriceCZKRow
 * *
 */
class PriceEURRow extends AbstractScheduleItemRow {
    /**
     * @param AbstractModelSingle|ModelScheduleItem $model
     * @return Html
     * @throws UnsupportedCurrencyException
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return Html::el('span')->addText($model->getPrice(Price::CURRENCY_EUR)->__toString());
    }

    public function getTitle(): string {
        return _('Price EUR');
    }
}
