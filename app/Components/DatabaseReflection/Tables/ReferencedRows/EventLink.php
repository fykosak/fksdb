<?php

namespace FKSDB\Components\DatabaseReflection\ReferencedRows;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\IEventReferencedModel;
use Nette\Application\BadRequestException;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\InvalidLinkException;
use Nette\Localization\ITranslator;
use Nette\Utils\Html;

/**
 * Class PersonLinkRow
 * @package FKSDB\Components\DatabaseReflection\VirtualRows
 */
class EventLink extends AbstractRow {

    /**
     * @var LinkGenerator
     */
    private $presenterComponent;

    /**
     * PersonLinkRow constructor.
     * @param ITranslator $translator
     * @param LinkGenerator $presenterComponent
     */
    public function __construct(ITranslator $translator, LinkGenerator $presenterComponent) {
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
            throw new BadTypeException(IEventReferencedModel::class, $model);
        }
        return Html::el('a')->addAttributes(['href' => $this->presenterComponent->link(
            'Event:Dashboard:default', ['eventId' => $model->getEvent()->event_id]
        )])->addText($model->getEvent()->name);
    }
}
