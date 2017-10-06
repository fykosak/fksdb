<?php

namespace FKSDB\Components\Controls\BrawlNav;

use Nette\Application\UI\Control;
use Nette\Diagnostics\Debugger;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Červeňák <miso@fykos.cz>
 */
class BrawlNav extends Control {

    const EVENT_TYPE_ID = 1;
    /**
     * @var \ModelEvent
     */
    private $event;

    /**
     *
     */
    private $eventId;
    /**
     * @var \ModelEvent[]
     */
    private $brawls;
    /**
     * @var \ServiceEvent
     */
    private $serviceEvent;
    /**
     * @var bool
     */
    private $initialized = false;


    function __construct(\ServiceEvent $serviceEvent) {
        parent::__construct();
        $this->serviceEvent = $serviceEvent;
    }

    /**
     * @param $params object
     * Redirect to correct address according to the resolved values.
     */
    public function syncRedirect($params) {
        $this->init($params);
        $eventId = isset($this->eventId) ? $this->eventId : null;
        if ($eventId != $params->eventId) {
            $this->getPresenter()->redirect('this', ['eventId' => $eventId]);
        }
    }

    /**
     * @return integer
     */
    public function getEventId() {
        return $this->eventId;
    }

    /**
     * @param $params object
     */
    protected function init($params) {
        if ($this->initialized) {
            return;
        }
        $this->initialized = true;
        $availableEventIds = $this->getBrawlIds();
        if ($params->eventId != -1 && $params->eventId != null) {

            if (in_array($params->eventId, $availableEventIds)) {
                $this->eventId = $params->eventId;
                return;
            }
        }
        $this->eventId = array_pop($availableEventIds);
        $this->event = $this->serviceEvent->findByPrimary($this->eventId);
    }

    /**
     * @return \ModelContest[]
     */
    private function getBrawls() {
        if ($this->brawls === null) {
            $this->brawls = [];
            $query = $this->serviceEvent->getTable()->where('event_type_id=?', self::EVENT_TYPE_ID)->order('event_year');
            foreach ($query as $event) {
                $this->brawls[] = $event;
            }
        }
        return $this->brawls;
    }

    /**
     * @return integer[]
     */
    private function getBrawlIds() {
        $events = $this->getBrawls();
        $ids = array_map(function (\ModelEvent $event) {
            return $event->event_id;
        }, $events);
        return $ids;
    }

    /**
     * @return \ModelEvent
     */
    private function getEvent() {
        if (!$this->event) {
            $this->event = $this->serviceEvent->findByPrimary($this->getEventId());
        }
        return $this->event;
    }

    public function render() {
        $this->template->availableBrawls = $this->getBrawls();
        $this->template->currentEvent = $this->getEvent();
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'BrawlNav.latte');
        $this->template->render();
    }

    public function handleChange($eventId) {
        $presenter = $this->getPresenter();
        $presenter->redirect('this', ['eventId' => $eventId]);
    }
}
