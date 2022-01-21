<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Payment;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\ModelPayment;
use Fykosak\NetteORM\AbstractModel;
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
     * @param ModelPayment $model
     * @throws \Exception
     */
    protected function createHtmlValue(AbstractModel $model): Html
    {
        return Html::el('span')->addHtml($model->getCurrency()->getLabel());
    }
}
