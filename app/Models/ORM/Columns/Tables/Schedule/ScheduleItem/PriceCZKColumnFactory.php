<?php

namespace FKSDB\Models\ORM\Columns\Tables\Schedule\ScheduleItem;

use FKSDB\Components\Badges\NotSetBadge;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\Models\Payment\PriceCalculator\UnsupportedCurrencyException;
use Fykosak\Utils\Price\Currency;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Utils\Html;

class PriceCZKColumnFactory extends ColumnFactory
{
    /**
     * @param AbstractModel|ModelScheduleItem $model
     * @throws UnsupportedCurrencyException|\Exception
     */
    protected function createHtmlValue(AbstractModel $model): Html
    {
        if (!$model->price_czk) {
            return NotSetBadge::getHtml();
        }
        return Html::el('span')->addText($model->getPrice(Currency::from(Currency::CZK))->__toString());
    }

    protected function createFormControl(...$args): BaseControl
    {
        $control = new TextInput($this->getTitle());
        $control->setHtmlType('number')
            ->setHtmlAttribute('step', '0.01');
        return $control;
    }
}
