<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Event;

use FKSDB\Models\ORM\Columns\Types\AbstractColumnFactory;
use FKSDB\Models\ORM\Models\EventModel;
use Fykosak\NetteORM\Model\Model;
use Nette\Utils\Html;

/**
 * @phpstan-extends AbstractColumnFactory<EventModel>
 */
class EventName extends AbstractColumnFactory
{
    protected function createHtmlValue(Model $model): Html
    {
        return Html::el('span')->addText($this->translator->getVariant($model->getName()));// @phpstan-ignore-line
    }
}
