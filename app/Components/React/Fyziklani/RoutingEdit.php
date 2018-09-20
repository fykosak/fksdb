<?php

namespace FKSDB\Components\React\Fyziklani;

use Nette\Utils\Json;

/**
 * Class Routing
 */
class RoutingEdit extends FyziklaniModule {
    /**
     * @var array
     */
    private $data;
    /**
     * @var \ServiceBrawlTeamPosition
     */
    private $serviceBrawlTeamPosition;


    public function __construct(
        $mode,
        \ServiceBrawlRoom $serviceBrawlRoom,
        \ServiceBrawlTeamPosition $serviceBrawlTeamPosition,
        \ModelEvent $event
    ) {
        parent::__construct($serviceBrawlRoom, $event);

        $this->serviceBrawlTeamPosition = $serviceBrawlTeamPosition;
        //$this->mode = $mode;
    }

    public function setData($data) {
        $this->data = $data;
    }

    public function getData() {
        return Json::encode($this->data);
    }

    protected function getMode() {
        return null;
    }

    protected function getComponentName() {
        return 'routing';
    }
}
