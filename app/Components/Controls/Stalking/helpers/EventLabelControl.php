<?php


namespace FKSDB\Components\Controls\Stalking\Helpers;


use FKSDB\ORM\Models\ModelEvent;
use Nette\Application\UI\Control;
use Nette\Templating\FileTemplate;

/**
 * Class EventLabelControl
 * @package FKSDB\Components\Controls\Stalking\Helpers
 * @property FileTemplate $template
 */
class EventLabelControl extends Control {
    /**
     * @param ModelEvent $event
     */
    public function render(ModelEvent $event) {
        $this->template->event = $event;
        $this->template->setFile(__DIR__ . '/EventLabelControl.latte');
        $this->template->render();
    }
}
