<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Event;

use FKSDB\Models\ORM\Columns\Types\AbstractColumnFactory;
use FKSDB\Models\ORM\Models\EventModel;
use Fykosak\NetteORM\Model;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\InvalidLinkException;
use Nette\Utils\Html;

/**
 * @phpstan-extends AbstractColumnFactory<EventModel,never>
 */
class EventLink extends AbstractColumnFactory
{
    private LinkGenerator $linkGenerator;

    final public function injectLink(LinkGenerator $linkGenerator): void
    {
        $this->linkGenerator = $linkGenerator;
    }

    /**
     * @param EventModel $model
     * @throws InvalidLinkException
     */
    protected function createHtmlValue(Model $model): Html
    {
        return Html::el('a')->addAttributes([
            'href' => $this->linkGenerator->link('Event:Dashboard:default', ['eventId' => $model->event_id]),
        ])->addText($model->name);
    }
}
