<?php

namespace FKSDB\DBReflection\ColumnFactories\Tables\Event;

use FKSDB\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\DBReflection\MetaDataFactory;
use FKSDB\ORM\Models\AbstractModelSingle;
use Nette\Application\LinkGenerator;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Application\UI\InvalidLinkException;
use Nette\Utils\Html;

/**
 * Class EventLink
 * @author Michal Červeňák <miso@fykos.cz>
 */
class EventLink extends DefaultColumnFactory {

    private LinkGenerator $linkGenerator;

    public function __construct(LinkGenerator $linkGenerator, MetaDataFactory $metaDataFactory) {
        parent::__construct($metaDataFactory);
        $this->linkGenerator = $linkGenerator;
    }

    /**
     * @param ModelEvent|AbstractModelSingle $model
     * @return Html
     * @throws InvalidLinkException
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return Html::el('a')->addAttributes(['href' => $this->linkGenerator->link(
            'Event:Dashboard:default', ['eventId' => $model->event_id]
        )])->addText($model->name);
    }
}
