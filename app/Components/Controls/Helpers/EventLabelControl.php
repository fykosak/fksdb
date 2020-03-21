<?php

namespace FKSDB\Components\Controls\Stalking\Helpers;

use FKSDB\ORM\Models\ModelEvent;
use Nette\Application\UI\Control;
use Nette\Application\UI\PresenterComponent;
use Nette\Templating\FileTemplate;
use Nette\Utils\Html;

/**
 * Class EventLabelControl
 * @package FKSDB\Components\Controls\Stalking\Helpers
 * @property FileTemplate $template
 * @deprecated
 */
class EventLabelControl extends Control {
    /**
     * @param ModelEvent $event
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    public function render(ModelEvent $event) {
        $this->template->html = self::getHtml($this, $event);
        $this->template->setFile(__DIR__ . '/EventLabelControl.latte');
        $this->template->render();
    }

    /**
     * @param PresenterComponent $component
     * @param ModelEvent $event
     * @return Html
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    public static function getHtml(PresenterComponent $component, ModelEvent $event) {
        return Html::el('a')->addAttributes([
            'href' => $component->getPresenter()->link(':Event:Dashboard:', ['eventId' => $event->event_id]),
        ])->addText($event->__toString());
    }
}
