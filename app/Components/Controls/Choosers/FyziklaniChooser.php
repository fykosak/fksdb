<?php

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventType;
use FKSDB\ORM\Services\ServiceEvent;
use FKSDB\ORM\Tables\TypedTableSelection;
use Nette\Application\AbortException;
use Nette\Application\UI\Control;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Červeňák <miso@fykos.cz>
 */
class FyziklaniChooser extends Control {
    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * @var ServiceEvent
     */
    private $serviceEvent;

    /**
     * FyziklaniChooser constructor.
     * @param ServiceEvent $serviceEvent
     * @param ModelEvent $event
     */
    function __construct(ServiceEvent $serviceEvent, ModelEvent $event) {
        parent::__construct();
        $this->serviceEvent = $serviceEvent;
        $this->event = $event;
    }

    /**
     * @return TypedTableSelection
     */
    private function getAllFyziklani(): TypedTableSelection {
        return $this->serviceEvent->getTable()->where('event_type_id=?', ModelEventType::FYZIKLANI)->order('event_year DESC');
    }

    public function render() {
        $this->template->availableFyziklani = $this->getAllFyziklani();
        $this->template->currentEvent = $this->event;
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'FyziklaniChooser.latte');
        $this->template->render();
    }

    /**
     * @param $eventId
     * @throws AbortException
     */
    public function handleChange($eventId) {
        $presenter = $this->getPresenter();
        $presenter->redirect('this', ['eventId' => $eventId]);
    }
}
