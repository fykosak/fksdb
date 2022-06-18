<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Schedule\ScheduleItem;

use FKSDB\Components\Badges\NotSetBadge;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\Models\Payment\PriceCalculator\UnsupportedCurrencyException;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Utils\Html;

class PriceEURColumnFactory extends ColumnFactory
{
    /**
     * @param ModelScheduleItem $model
     * @throws UnsupportedCurrencyException|\Exception
     */
    protected function createHtmlValue(Model $model): Html
    {
        if (!$model->price_eur) {
            return NotSetBadge::getHtml();
        }
        return Html::el('span')->addText($model->getPrice()->eur->__toString());
    }

    protected function createFormControl(...$args): BaseControl
    {
        $control = new TextInput($this->getTitle());
        $control->setHtmlType('number')
            ->setHtmlAttribute('step', '0.01');
        return $control;
    }
}
