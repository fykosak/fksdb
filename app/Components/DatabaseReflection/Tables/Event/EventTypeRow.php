<?php

namespace FKSDB\Components\DatabaseReflection\Event;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\ServiceEventType;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Localization\ITranslator;
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
     * @param ITranslator $translator
     * @param ServiceEventType $serviceEventType
     */
    public function __construct(ITranslator $translator, ServiceEventType $serviceEventType) {
        parent::__construct($translator);
        $this->serviceEventType = $serviceEventType;
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Event type');
    }

    /**
     * @param array $args
     * @return BaseControl
     * @throws BadRequestException
     */
    public function createField(...$args): BaseControl {
        if (\is_null($contest)) {
            throw new BadRequestException();
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
