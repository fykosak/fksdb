<?php

namespace FKSDB\Components\React\Fyziklani;

use FKSDB\ORM\ModelEvent;
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
        ModelEvent $event
    ) {
        parent::__construct($serviceBrawlRoom, $event);

        $this->serviceBrawlTeamPosition = $serviceBrawlTeamPosition;
    }

    public function setData($data) {
        $this->data = $data;
    }

    public function getData(): string {
        return Json::encode($this->data);
    }

    public function getMode(): string {
        return null;
    }

    public function getComponentName(): string {
        return 'routing';
    }
}
