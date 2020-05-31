<?php

namespace FKSDB\Components\DatabaseReflection\Event;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\ServiceEventType;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Utils\Html;

/**
 * Class EventTypeRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class EventTypeRow extends AbstractRow {

    private ServiceEventType $serviceEventType;

    /**
     * EventTypeRow constructor.
     * @param ServiceEventType $serviceEventType
     */
    public function __construct(ServiceEventType $serviceEventType) {
        $this->serviceEventType = $serviceEventType;
    }

    public function getTitle(): string {
        return _('Event type');
    }

    /**
     * @param array $args
     * @return BaseControl
     * @throws \InvalidArgumentException
     */
    public function createField(...$args): BaseControl {
        list($contest) = $args;
        if (\is_null($contest) || !$contest instanceof ModelContest) {
            throw new \InvalidArgumentException();
        }

        $element = new SelectBox($this->getTitle());

        $types = $this->serviceEventType->getTable()->where('contest_id', $contest->contest_id)->fetchPairs('event_type_id', 'name');
        $element->setItems($types);
        $element->setPrompt(_('Zvolit typ'));

        return $element;
    }

    /**
     * @param AbstractModelSingle|ModelEvent $model
     * @return Html
     */
    public function createHtmlValue(AbstractModelSingle $model): Html {
        return Html::el('span')->addText($model->getEventType()->name);
    }

    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

}
