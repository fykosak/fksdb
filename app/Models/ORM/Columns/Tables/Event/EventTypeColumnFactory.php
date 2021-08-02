<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Event;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\MetaDataFactory;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\ServiceEventType;
use Fykosak\NetteORM\AbstractModel;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Utils\Html;

class EventTypeColumnFactory extends ColumnFactory
{

    private ServiceEventType $serviceEventType;

    public function __construct(ServiceEventType $serviceEventType, MetaDataFactory $metaDataFactory)
    {
        parent::__construct($metaDataFactory);
        $this->serviceEventType = $serviceEventType;
    }

    /**
     * @param array $args
     * @return BaseControl
     * @throws \InvalidArgumentException
     */
    protected function createFormControl(...$args): BaseControl
    {
        [$contest] = $args;
        if (\is_null($contest) || !$contest instanceof ModelContest) {
            throw new \InvalidArgumentException();
        }

        $element = new SelectBox($this->getTitle());

        $types = $this->serviceEventType->getTable()->where('contest_id', $contest->contest_id)->fetchPairs(
            'event_type_id',
            'name'
        );
        $element->setItems($types);
        $element->setPrompt(_('Select event type'));

        return $element;
    }

    /**
     * @param AbstractModel|ModelEvent $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModel $model): Html
    {
        return Html::el('span')->addText($model->getEventType()->name);
    }
}
