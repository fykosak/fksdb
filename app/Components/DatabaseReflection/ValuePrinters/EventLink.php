<?php

namespace FKSDB\Components\DatabaseReflection\ValuePrinters;

use FKSDB\ORM\Models\IEventReferencedModel;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\PresenterComponent;
use Nette\Templating\FileTemplate;
use Nette\Utils\Html;

/**
 * Class EventLabelControl
 * @package FKSDB\Components\Controls\Stalking\Helpers
 * @property FileTemplate $template
 */
class EventLink extends AbstractValuePrinter {
    /**
     * @var PresenterComponent
     */
    private $presenterComponent;

    /**
     * PersonLink constructor.
     * @param PresenterComponent $presenterComponent
     */
    public function __construct(PresenterComponent $presenterComponent) {
        $this->presenterComponent = $presenterComponent;
    }

    /**
     * @param IEventReferencedModel|ModelEvent $model
     * @return Html
     * @throws InvalidLinkException
     * @throws BadRequestException
     */
    public function getHtml($model): Html {
        $event = null;
        if ($model instanceof IEventReferencedModel) {
            $event = $model->getEvent();
        } elseif ($model instanceof ModelEvent) {
            $event = $model;
        } else {
            throw new BadRequestException();
        }

        return Html::el('a')->addAttributes([
            'href' => $this->presenterComponent->getPresenter()->link(':Event:Dashboard:', ['eventId' => $event->event_id]),
        ])->addText($event->__toString());
    }
}
