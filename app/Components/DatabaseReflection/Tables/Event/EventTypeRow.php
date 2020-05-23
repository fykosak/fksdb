<?php

namespace FKSDB\Components\DatabaseReflection\Event;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\ServiceEventType;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Utils\Html;

/**
 * Class EventTypeRow
 * @package FKSDB\Components\DatabaseReflection\Event
 */
class EventTypeRow extends AbstractEventRowFactory {
    /**
     * @var ServiceEventType
     */
    private $serviceEventType;

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
        if (\is_null($contest)) {
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

}
