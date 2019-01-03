<?php

namespace FKSDB\Components\React\Fyziklani;

use FKSDB\Components\React\ReactComponent;
use FKSDB\ORM\ModelEvent;
use Nette\DI\Container;
use ORM\Services\Events\ServiceFyziklaniTeam;

abstract class FyziklaniModule extends ReactComponent {

    /**
     * @var \ServiceFyziklaniRoom
     */
    protected $serviceFyziklaniRoom;

    /**
     * @var \ServiceFyziklaniTeamPosition
     */
    protected $serviceFyziklaniTeamPosition;

    /**
     * @var ServiceFyziklaniTeam
     */
    protected $serviceFyziklaniTeam;

    /**
     * @var \ServiceFyziklaniTask
     */
    protected $serviceFyziklaniTask;
    /**
     * @var \ServiceFyziklaniSubmit
     */
    protected $serviceFyziklaniSubmit;

    /**
     * @var ModelEvent
     */
    protected $event;

    /**
     * @var Container
     */
    protected $context;

    public function __construct(
        Container $container,
        ModelEvent $event,
        \ServiceFyziklaniRoom $serviceFyziklaniRoom,
        \ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition,
        ServiceFyziklaniTeam $serviceFyziklaniTeam,
        \ServiceFyziklaniTask $serviceFyziklaniTask,
        \ServiceFyziklaniSubmit $serviceFyziklaniSubmit
    ) {
        parent::__construct($container);
        $this->event = $event;

        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->serviceFyziklaniTeamPosition = $serviceFyziklaniRoom;
        $this->serviceFyziklaniRoom = $serviceFyziklaniTeamPosition;

    }


    public final function getModuleName(): string {
        return 'fyziklani';
    }

    protected final function getEvent() {
        return $this->event;
    }

    /**
     * @return \ModelFyziklaniRoom[]
     */
    protected function getRooms() {
        return $this->serviceFyziklaniRoom->getRoomsByIds($this->getEvent()->getParameter('gameSetup')['rooms']);
    }
}
