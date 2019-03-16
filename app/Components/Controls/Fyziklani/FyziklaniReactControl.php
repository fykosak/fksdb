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
     * @var \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniRoom
     */
    protected $serviceFyziklaniRoom;

    /**
     * @var \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition
     */
    protected $serviceFyziklaniTeamPosition;

    /**
     * @var ServiceFyziklaniTeam
     */
    protected $serviceFyziklaniTeam;

    /**
     * @var \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask
     */
    protected $serviceFyziklaniTask;
    /**
     * @var \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit
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
     * @param \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniRoom $serviceFyziklaniRoom
     * @param \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     * @param \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask $serviceFyziklaniTask
     * @param \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit $serviceFyziklaniSubmit
     */
    public function __construct(
        Container $container,
        ModelEvent $event,
        \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniRoom $serviceFyziklaniRoom,
        \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition,
        ServiceFyziklaniTeam $serviceFyziklaniTeam,
        \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask $serviceFyziklaniTask,
        \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit $serviceFyziklaniSubmit
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
     * @return \FKSDB\ORM\Models\Fyziklani\ModelFyziklaniRoom[]
     */
    protected function getRooms() {
        return $this->serviceFyziklaniRoom->getRoomsByIds($this->getEvent()->getParameter('gameSetup')['rooms']);
    }
}
