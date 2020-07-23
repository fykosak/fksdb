<?php

namespace FKSDB\DBReflection\ColumnFactories\Event;

use FKSDB\DBReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\DBReflection\FieldLevelPermission;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Application\LinkGenerator;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Application\UI\InvalidLinkException;
use Nette\Utils\Html;

/**
 * Class EventLink
 * @author Michal Červeňák <miso@fykos.cz>
 */
class EventLink extends AbstractColumnFactory {
    /** @var LinkGenerator */
    private $linkGenerator;

    /**
     * PersonLinkRow constructor.
     * @param LinkGenerator $linkGenerator
     */
    public function __construct(LinkGenerator $linkGenerator) {
        $this->linkGenerator = $linkGenerator;
    }

    public function getPermission(): FieldLevelPermission {
        return new FieldLevelPermission(self::PERMISSION_ALLOW_ANYBODY, self::PERMISSION_ALLOW_ANYBODY);
    }

    public function getTitle(): string {
        return _('Event');
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
