<?php

namespace FKSDB\Components\DatabaseReflection\ReferencedRows;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\PresenterComponent;
use Nette\Utils\Html;

/**
 * Class PersonLinkRow
 * *
 */
class EventLink extends AbstractRow {

    /**
     * @var PresenterComponent
     */
    private $presenterComponent;

    /**
     * PersonLinkRow constructor.
     * @param PresenterComponent $presenterComponent
     */
    public function __construct(PresenterComponent $presenterComponent) {
        $this->presenterComponent = $presenterComponent;
    }

    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
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
        return Html::el('a')->addAttributes(['href' => $this->presenterComponent->getPresenter()->link(
            ':Event:Dashboard:default', ['eventId' => $model->event_id]
        )])->addText($model->name);
    }
}
