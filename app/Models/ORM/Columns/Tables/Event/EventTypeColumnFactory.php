<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Event;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\EventModel;
use Fykosak\NetteORM\Model\Model;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<EventModel,ContestModel>
 */
class EventTypeColumnFactory extends ColumnFactory
{

    /**
     * @throws BadTypeException
     */
    protected function createFormControl(...$args): BaseControl
    {
        [$contest] = $args;
        if (!$contest instanceof ContestModel) {
            throw new BadTypeException(ContestModel::class, $contest);
        }

        $element = new SelectBox($this->getTitle());

        $types = $contest->getEventTypes()->fetchPairs(
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
