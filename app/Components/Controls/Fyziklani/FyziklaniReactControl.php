<?php

namespace FKSDB\Components\Controls\Fyziklani;

use FKSDB\Components\React\ReactComponent;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniRoom;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniRoom;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition;
use Nette\DI\Container;
use Nette\NotImplementedException;

/**
 * Class FyziklaniReactControl
 * @package FKSDB\Components\Controls\Fyziklani
 */
abstract class FyziklaniReactControl extends ReactComponent {

    /**
     * @var ServiceFyziklaniRoom
     */
    protected $serviceFyziklaniRoom;

    /**
     * @var ServiceFyziklaniTeamPosition
     */
    protected $serviceFyziklaniTeamPosition;

    /**
     * @var ServiceFyziklaniTeam
     */
    protected $serviceFyziklaniTeam;

    /**
     * @var ServiceFyziklaniTask
     */
    protected $serviceFyziklaniTask;
    /**
     * @var ServiceFyziklaniSubmit
     */
    protected $serviceFyziklaniSubmit;

    /**
     * @var \FKSDB\ORM\Models\ModelEvent
     */
    protected $event;

    /**
     * @var Container
     */
    protected $context;

    /**
     * FyziklaniReactControl constructor.
     * @param Container $container
     * @param \FKSDB\ORM\Models\ModelEvent $event
     * @param ServiceFyziklaniRoom $serviceFyziklaniRoom
     * @param ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     * @param ServiceFyziklaniTask $serviceFyziklaniTask
     * @param ServiceFyziklaniSubmit $serviceFyziklaniSubmit
     */
    public function __construct(
        Container $container,
        ModelEvent $event,
        ServiceFyziklaniRoom $serviceFyziklaniRoom,
        ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition,
        ServiceFyziklaniTeam $serviceFyziklaniTeam,
        ServiceFyziklaniTask $serviceFyziklaniTask,
        ServiceFyziklaniSubmit $serviceFyziklaniSubmit
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
    protected function getReactId(): string {
        return 'fyziklani.' . $this->getComponentName() . '.' . $this->getMode();
    }

    /**
     * @return string
     */
    abstract protected function getComponentName(): string;

    /**
     * @return string
     */
    abstract protected function getMode(): string;

    /**
     * @return \FKSDB\ORM\Models\ModelEvent
     */
    protected final function getEvent() {
        return $this->event;
    }

    /**
     * @return ModelFyziklaniRoom[]
     */
    protected function getRooms() {
        return $this->serviceFyziklaniRoom->getRoomsByIds($this->getEvent()->getParameter('gameSetup')['rooms']);
    }
}
