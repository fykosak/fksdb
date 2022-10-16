<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Event;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\MetaDataFactory;
use Fykosak\NetteORM\Model;
use Nette\Application\LinkGenerator;
use FKSDB\Models\ORM\Models\EventModel;
use Nette\Application\UI\InvalidLinkException;
use Nette\Utils\Html;

class EventLink extends ColumnFactory
{
    private LinkGenerator $linkGenerator;

    public function __construct(LinkGenerator $linkGenerator, MetaDataFactory $metaDataFactory)
    {
        parent::__construct($metaDataFactory);
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
