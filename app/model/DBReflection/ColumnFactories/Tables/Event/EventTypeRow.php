<?php

namespace FKSDB\DBReflection\ColumnFactories\Event;

use FKSDB\DBReflection\ColumnFactories\DefaultColumnFactory;
use FKSDB\DBReflection\MetaDataFactory;
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
class EventTypeRow extends DefaultColumnFactory {

    private ServiceEventType $serviceEventType;

    /**
     * EventTypeRow constructor.
     * @param ServiceEventType $serviceEventType
     * @param MetaDataFactory $metaDataFactory
     */
    public function __construct(ServiceEventType $serviceEventType, MetaDataFactory $metaDataFactory) {
        parent::__construct($metaDataFactory);
        $this->serviceEventType = $serviceEventType;
    }

    /**
     * @param array $args
     * @return BaseControl
     * @throws \InvalidArgumentException
     */
    protected function createFormControl(...$args): BaseControl {
        [$contest] = $args;
        if (\is_null($contest) || !$contest instanceof ModelContest) {
            throw new \InvalidArgumentException();
        }

        $element = new SelectBox($this->getTitle());

        $types = $this->serviceEventType->getTable()->where('contest_id', $contest->contest_id)->fetchPairs('event_type_id', 'name');
        $element->setItems($types);
        $element->setPrompt(_('Select event type'));

        return $element;
    }

    /**
     * @param AbstractModelSingle|ModelEvent $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return Html::el('span')->addText($model->getEventType()->name);
    }
}
