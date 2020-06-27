<?php

namespace FKSDB\Components\DatabaseReflection\Tables\Teacher;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\Components\DatabaseReflection\FieldLevelPermission;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelTeacher;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Utils\Html;

/**
 * Class StateRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class StateRow extends AbstractColumnFactory {

    /**
     * @param AbstractModelSingle|ModelTeacher $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        $items = $this->getItems();
        if (!array_key_exists($model->state, $items)) {
            return NotSetBadge::getHtml();
        }
        $el = Html::el('span');
        switch ($model->state) {
            case 'proposal':
                $el->addAttributes(['class' => 'badge badge-info']);
                break;
            case 'cooperate':
                $el->addAttributes(['class' => 'badge badge-success']);
                break;
            case 'ended':
                $el->addAttributes(['class' => 'badge badge-dark']);
                break;
            case 'undefined':
                $el->addAttributes(['class' => 'badge badge-secondary']);
                break;
        }
        $el->addText($this->getItems()[$model->state]);
        return $el;
    }

    public function getTitle(): string {
        return _('Cooperation status');
    }

    /**
     * @param array $args
     * @return BaseControl
     */
    public function createField(...$args): BaseControl {
        return new SelectBox($this->getTitle(), $this->getItems());
    }

    private function getItems(): array {
        return [
            'proposal' => _('Proposal'),
            'cooperate' => _('Cooperate'),
            'ended' => _('Ended'),
            'undefined' => _('Undefined')
        ];
    }
    public function getPermission(): FieldLevelPermission {
        return new FieldLevelPermission(self::PERMISSION_ALLOW_ANYBODY, self::PERMISSION_ALLOW_ANYBODY);
    }
}
