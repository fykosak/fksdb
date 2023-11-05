<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Schedule\ScheduleItem;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\Payment\PriceCalculator\UnsupportedCurrencyException;
use Fykosak\NetteORM\Model;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<ScheduleItemModel,never>
 */
class PriceCZKColumnFactory extends ColumnFactory
{
    /**
     * @param ScheduleItemModel $model
     * @throws UnsupportedCurrencyException|\Exception
     */
    protected function createHtmlValue(Model $model): Html
    {
        if (!$model->price_eur) {
            return Html::el('span')->addAttributes(['class' => 'badge bg-success'])->addText(_('For free'));
        }
        return Html::el('span')->addText($model->getPrice()->czk->__toString());// @phpstan-ignore-line
    }

    protected function createFormControl(...$args): BaseControl
    {
        $control = new TextInput($this->getTitle());
        $control->setHtmlType('number')
            ->setHtmlAttribute('step', '0.01');
        return $control;
    }
}
