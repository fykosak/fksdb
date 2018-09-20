<?php

namespace FKSDB\Components\React\Fyziklani;

use FKSDB\Components\React\ReactComponent;

abstract class FyziklaniModule extends ReactComponent {

    /**
     * @var \ServiceBrawlRoom
     */
    private $serviceBrawlRoom;

    /**
     * @var \ModelEvent
     */
    private $event;

    public function __construct(\ServiceBrawlRoom $serviceBrawlRoom, \ModelEvent $event) {
        parent::__construct();
        $this->serviceBrawlRoom = $serviceBrawlRoom;
        $this->event = $event;
    }

    protected final function getModuleName() {
        return 'fyziklani';
    }

    protected final function getEvent() {
        return $this->event;
    }

    /**
     * @return \ModelBrawlRoom[]
     */
    protected function getRooms() {
        return $this->serviceBrawlRoom->getRoomsByIds($this->getEvent()->getParameter('rooms'));
    }
}
