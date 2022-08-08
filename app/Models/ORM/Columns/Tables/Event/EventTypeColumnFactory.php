<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Event;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\MetaDataFactory;
use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\EventTypeService;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Utils\Html;

class EventTypeColumnFactory extends ColumnFactory
{
    private EventTypeService $eventTypeService;

    public function __construct(EventTypeService $eventTypeService, MetaDataFactory $metaDataFactory)
    {
        parent::__construct($metaDataFactory);
        $this->eventTypeService = $eventTypeService;
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function createFormControl(...$args): BaseControl
    {
        [$contest] = $args;
        if (!$contest instanceof ContestModel) {
            throw new \InvalidArgumentException();
        }

        $element = new SelectBox($this->getTitle());

        $types = $this->eventTypeService->getTable()->where('contest_id', $contest->contest_id)->fetchPairs(
            'event_type_id',
            'name'
        );
        $element->setItems($types);
        $element->setPrompt(_('Select event type'));

        return $element;
    }

    /**
     * @param EventModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        return Html::el('span')->addText($model->event_type->name);
    }
}
