<?php

namespace FKSDB\Components\DatabaseReflection\EmailMessage;

use FKSDB\Components\Controls\Helpers\Badges\NotSetBadge;
use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelEmailMessage;
use Nette\Utils\Html;

/**
 * Class StateRow
 * @package FKSDB\Components\DatabaseReflection\EmailMessage
 */
class StateRow extends AbstractRow {
    /**
     * @var array
     */
    private $classNameMapping = [
        'badge badge-1' => [ModelEmailMessage::STATE_SAVED],
        'badge badge-2' => [ModelEmailMessage::STATE_WAITING],
        'badge badge-3' => [ModelEmailMessage::STATE_SENT],
        'badge badge-4' => [ModelEmailMessage::STATE_FAILED],
        'badge badge-5' => [],
        'badge badge-6' => [ModelEmailMessage::STATE_CANCELED],
        'badge badge-7' => [],
        'badge badge-8' => [],
        'badge badge-9' => [],
        'badge badge-10' => [],
    ];

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('State');
    }

    /**
     * @param ModelEmailMessage|AbstractModelSingle $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        $state = $model->state;
        if (\is_null($state)) {
            return NotSetBadge::getHtml();
        }
        $elementClassName = '';
        foreach ($this->classNameMapping as $className => $states) {
            if (\in_array($state, $states)) {
                $elementClassName = $className;
            }
        }
        return Html::el('span')->addAttributes(['class' => $elementClassName])->addText(_($state));
    }

    /**
     * @inheritDoc
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }
}
