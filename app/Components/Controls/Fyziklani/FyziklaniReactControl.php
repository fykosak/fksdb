<?php

namespace FKSDB\Components\Controls\Fyziklani;

use FKSDB\Components\React\ReactComponent;
use FKSDB\ORM\ModelEvent;
use Nette\DI\Container;
use ORM\Services\Events\ServiceFyziklaniTeam;

/**
 * Class FyziklaniReactControl
 * @package FKSDB\Components\Controls\Fyziklani
 */
abstract class FyziklaniReactControl extends ReactComponent {

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

    /**
     * FyziklaniReactControl constructor.
     * @param Container $container
     * @param ModelEvent $event
     * @param \ServiceFyziklaniRoom $serviceFyziklaniRoom
     * @param \ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     * @param \ServiceFyziklaniTask $serviceFyziklaniTask
     * @param \ServiceFyziklaniSubmit $serviceFyziklaniSubmit
     */
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
        $this->serviceFyziklaniTeamPosition = $serviceFyziklaniTeamPosition;
        $this->serviceFyziklaniRoom = $serviceFyziklaniRoom;

    }


    /**
     * @return string
     */
    public final function getModuleName(): string {
        return 'fyziklani';
    }

    /**
     * @return ModelEvent
     */
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
