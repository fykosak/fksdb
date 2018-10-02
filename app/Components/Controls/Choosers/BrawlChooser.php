<?php

namespace FKSDB\Components\Controls\Choosers;

use Nette\Application\UI\Control;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Červeňák <miso@fykos.cz>
 */
class BrawlChooser extends Control {

    const EVENT_TYPE_ID = 1;
    /**
     * @var \FKSDB\ORM\ModelEvent
     */
    private $event;

    /**
     * @var \FKSDB\ORM\ModelEvent[]
     */
    private $brawls;
    /**
     * @var \ServiceEvent
     */
    private $serviceEvent;

    function __construct(\ServiceEvent $serviceEvent) {
        parent::__construct();
        $this->serviceEvent = $serviceEvent;
    }

    public function setEvent(\FKSDB\ORM\ModelEvent $event) {
        $this->event = $event;
    }
    /**
     * @return \FKSDB\ORM\ModelEvent
     */
    private function getEvent() {
        return $this->event;
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

    public function render() {
        $this->template->availableBrawls = $this->getBrawls();
        $this->template->currentEvent = $this->getEvent();
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'BrawlChooser.latte');
        $this->template->render();
    }

    public function handleChange($eventId) {
        $presenter = $this->getPresenter();
        $presenter->redirect('this', ['eventID' => $eventId]);
    }
}
