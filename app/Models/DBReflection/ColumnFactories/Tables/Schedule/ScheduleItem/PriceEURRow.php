<?php

namespace FKSDB\Models\DBReflection\ColumnFactories\Tables\Schedule\ScheduleItem;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\Models\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\Models\Payment\Price;
use FKSDB\Models\Payment\PriceCalculator\UnsupportedCurrencyException;
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
        $control->setHtmlType('number')
            ->setHtmlAttribute('step', '0.01');
        return $control;
    }
}
