<?php

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\ORM\ModelEvent;
use Nette\Application\UI\Control;
use ServiceEvent;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Červeňák <miso@fykos.cz>
 */
class FyziklaniChooser extends Control {

    const EVENT_TYPE_ID = 1;
    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * @var ServiceEvent
     */
    private $serviceEvent;

    function __construct(ServiceEvent $serviceEvent) {
        parent::__construct();
        $this->serviceEvent = $serviceEvent;
    }

    public function setEvent(ModelEvent $event) {
        $this->event = $event;
    }

    /**
     * @return ModelEvent[]
     */
    private function getAllFyziklani(): array {
        $events = [];
        $query = $this->serviceEvent->getTable()->where('event_type_id=?', self::EVENT_TYPE_ID)->order('event_year DESC');
        foreach ($query as $row) {
            $events[] = ModelEvent::createFromTableRow($row);
        }
        return $events;
    }

    public function render() {
        $this->template->availableFyziklani = $this->getAllFyziklani();
        $this->template->currentEvent = $this->event;
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'FyziklaniChooser.latte');
        $this->template->render();
    }

    public function handleChange($eventId) {
        $presenter = $this->getPresenter();
        $presenter->redirect('this', ['eventId' => $eventId]);
    }
}
