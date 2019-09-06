<?php

namespace FKSDB\Components\DatabaseReflection\ReferencedRows;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\Transitions\IEventReferencedModel;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\PresenterComponent;
use Nette\Localization\ITranslator;
use Nette\Utils\Html;

/**
 * Class PersonLinkRow
 * @package FKSDB\Components\DatabaseReflection\VirtualRows
 */
class EventLinkRow extends AbstractRow {

    /**
     * @var PresenterComponent
     */
    private $presenterComponent;

    /**
     * PersonLinkRow constructor.
     * @param ITranslator $translator
     * @param PresenterComponent $presenterComponent
     */
    public function __construct(ITranslator $translator, PresenterComponent $presenterComponent) {
        parent::__construct($translator);
        $this->presenterComponent = $presenterComponent;
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Event');
    }

    /**
     * @param AbstractModelSingle $model
     * @return Html
     * @throws BadRequestException
     * @throws InvalidLinkException
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        if (!$model instanceof IEventReferencedModel) {
            throw new BadRequestException();
        }
        $event = $model->getEvent();
        return Html::el('a')
            ->addAttributes(['href' => $this->presenterComponent->getPresenter()->link(':Common:Stalking:view', [
                'id' => $event->event_id,
            ])])
            ->addText($event->name);

    }
}
