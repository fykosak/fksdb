<?php

namespace FKSDB\Models\ORM\Columns\ColumnFactories\Tables\Person;

use FKSDB\Models\ORM\Columns\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\ModelPerson;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\RadioList;
use Nette\Utils\Html;

/**
 * Class GenderRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class GenderColumnFactory extends DefaultColumnFactory {

    /**
     * @param array $args
     * @return BaseControl
     */
    protected function createFormControl(...$args): BaseControl {
        $control = new RadioList($this->getTitle(), $this->createOptions());
        $control->setDefaultValue('M');
        return $control;
    }

    private function createOptions(): array {
        return ['M' => _('Male'), 'F' => _('Female')];
    }

    /**
     * @param AbstractModelSingle|ModelPerson $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        if ($model->gender == 'F') {
            return Html::el('span')->addAttributes(['class' => 'fa fa-venus']);
        } elseif ($model->gender == 'M') {
            return Html::el('span')->addAttributes(['class' => 'fa fa-mars']);
        } else {
            return Html::el('span')->addAttributes(['class' => 'fa fa-transgender']);
        }
    }
}
