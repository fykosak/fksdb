<?php

namespace FKSDB\DBReflection\ColumnFactories\Schedule\ScheduleItem;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\DBReflection\ColumnFactories\DefaultColumnFactory;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\Payment\Price;
use FKSDB\Payment\PriceCalculator\UnsupportedCurrencyException;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Utils\Html;

/**
 * Class PriceEURRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PriceEURRow extends DefaultColumnFactory {
    /**
     * @param AbstractModelSingle|ModelScheduleItem $model
     * @return Html
     * @throws UnsupportedCurrencyException
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        if (!$model->price_eur) {
            return NotSetBadge::getHtml();
        }
        return Html::el('span')->addText($model->getPrice(Price::CURRENCY_EUR)->__toString());
    }

    protected function createFormControl(...$args): BaseControl {
        $control = new TextInput($this->getTitle());
        $control->setType('number')
            ->setAttribute('step', '0.01');
        return $control;
    }
}
