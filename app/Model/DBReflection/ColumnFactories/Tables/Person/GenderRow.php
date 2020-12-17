<?php

namespace FKSDB\Model\DBReflection\ColumnFactories\Tables\Person;

use FKSDB\Model\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\Model\ORM\Models\AbstractModelSingle;
use FKSDB\Model\ORM\Models\ModelPerson;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\RadioList;
use Nette\Utils\Html;

/**
 * Class GenderRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class GenderRow extends DefaultColumnFactory {

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
