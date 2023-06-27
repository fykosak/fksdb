<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Payment;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\Warehouse\ItemModel;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Price\Currency;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Utils\Html;

class CurrencyColumnFactory extends ColumnFactory
{
    protected function createFormControl(...$args): BaseControl
    {
        $control = new SelectBox($this->getTitle());
        $items = [];
        foreach (Currency::cases() as $currency) {
            $items[$currency->value] = $currency->getLabel();
        }
        $control->setItems($items)->setPrompt(_('Select currency'));
        return $control;
    }

    /**
     * @param PaymentModel|ItemModel $model
     * @throws \Exception
     */
    protected function createHtmlValue(Model $model): Html
    {
        return Html::el('span')->addHtml($model->getCurrency()->getLabel());
    }
}
