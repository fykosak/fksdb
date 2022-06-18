<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Person;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\ModelPerson;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\RadioList;
use Nette\Utils\Html;

class GenderColumnFactory extends ColumnFactory
{

    protected function createFormControl(...$args): BaseControl
    {
        $control = new RadioList($this->getTitle(), $this->createOptions());
        $control->setDefaultValue('M');
        return $control;
    }

    private function createOptions(): array
    {
        return ['M' => _('Male'), 'F' => _('Female')];
    }

    /**
     * @param ModelPerson $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        if ($model->gender == 'F') {
            return Html::el('span')->addAttributes(['class' => 'fa fa-venus']);
        } elseif ($model->gender == 'M') {
            return Html::el('span')->addAttributes(['class' => 'fa fa-mars']);
        } else {
            return Html::el('span')->addAttributes(['class' => 'fa fa-transgender']);
        }
    }
}
