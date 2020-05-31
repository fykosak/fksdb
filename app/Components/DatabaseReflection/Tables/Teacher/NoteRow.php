<?php

namespace FKSDB\Components\DatabaseReflection\Tables\Teacher;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelTeacher;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * Class NoteRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class NoteRow extends AbstractTeacherRow {

    /**
     * @param AbstractModelSingle|ModelTeacher $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return Html::el('span')->addText($model->note);
    }

    public function getTitle(): string {
        return _('Note');
    }

    /**
     * @param array $args
     * @return BaseControl
     */
    public function createField(...$args): BaseControl {
        return (new TextInput(_('Note')))->addRule(Form::MAX_LENGTH, null, 255);
    }
}
