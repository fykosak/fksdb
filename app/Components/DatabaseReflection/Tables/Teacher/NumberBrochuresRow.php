<?php

namespace FKSDB\Components\DatabaseReflection\Tables\Teacher;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelTeacher;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Class NumberBrochuresRow
 * @package FKSDB\Components\DatabaseReflection\Tables\Teacher
 */
class NumberBrochuresRow extends AbstractTeacherRow {

    /**
     * @param AbstractModelSingle|ModelTeacher $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return Html::el('span')->addText($model->number_brochures);
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Number of brochures');
    }

    /**
     * @return string|null
     */
    public function getDescription() {
        return _('Number of brochures/propagation items, that he wants to send.');
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        return (new TextInput(_('Number of brochures')))->addRule(Form::INTEGER)->setOption('description', $this->getDescription());
    }
}