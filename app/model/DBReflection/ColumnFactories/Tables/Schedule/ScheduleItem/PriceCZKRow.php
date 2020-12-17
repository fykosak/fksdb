<?php

namespace FKSDB\DBReflection\ColumnFactories\Tables\Schedule\ScheduleItem;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\ORM\Models\AbstractModelSingle;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\Payment\Price;
use FKSDB\Payment\PriceCalculator\UnsupportedCurrencyException;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Utils\Html;

/**
 * Class PriceCZKRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PriceCZKRow extends DefaultColumnFactory {
    /**
     * @param AbstractModelSingle|ModelScheduleItem $model
     * @return Html
     * @throws UnsupportedCurrencyException
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        if (!$model->price_czk) {
            return NotSetBadge::getHtml();
        }
        return Html::el('span')->addText($model->getPrice(Price::CURRENCY_CZK)->__toString());
    }

    protected function createFormControl(...$args): BaseControl {
        $control = new TextInput($this->getTitle());
        $control->setType('number')
            ->setAttribute('step', '0.01');
        return $control;
    }
}
