<?php

namespace FKSDB\Components\DatabaseReflection\Tables\Teacher;

use FKSDB\Components\Controls\Helpers\Badges\NotSetBadge;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelTeacher;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Utils\Html;

/**
 * Class StateRow
 * @package FKSDB\Components\DatabaseReflection\Tables\Teacher
 */
class StateRow extends AbstractTeacherRow {

    /**
     * @param AbstractModelSingle|ModelTeacher $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        $items = $this->getItems();
        if (!array_key_exists($model->state, $items)) {
            return NotSetBadge::getHtml();
        }
        return Html::el('span')->addAttributes(['class' => 'badge badge-default'])->addText($this->getItems()[$model->state]);
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('State');
    }

    /**
     * @return BaseControl
     */
    public function createField(): BaseControl {
        return new SelectBox($this->getTitle(), $this->getItems());
    }

    /**
     * @return array
     */
    private function getItems(): array {
        return [
            'proposal' => _('Proposal'),
            'cooperate' => _('Cooperate'),
            'ended' => _('Ended'),
        ];
    }
}
