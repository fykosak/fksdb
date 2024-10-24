<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\Transitions\Machine\EmailMachine;
use FKSDB\Models\Transitions\Machine\EventParticipantMachine;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Transitions\Machine\PaymentMachine;
use FKSDB\Models\Transitions\Machine\PersonScheduleMachine;
use FKSDB\Models\Transitions\Machine\TeamMachine;
use Nette\DI\Container;
use Nette\DI\MissingServiceException;

class TransitionsMachineFactory
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @throws MissingServiceException
     * @throws NotImplementedException
     */
    public function getParticipantMachine(EventModel $event): EventParticipantMachine
    {
        switch ($event->event_type_id) {
            case 4:
            case 5:
                return $this->container->getService('transitions.sous.machine');
            case 2:
            case 14:
                return $this->container->getService('transitions.dsef.machine');
            case 10:
                return $this->container->getService('transitions.tabor.machine');
            case 11:
            case 12:
                return $this->container->getService('transitions.setkani.machine');
            default:
                throw new NotImplementedException();
        }
    }

    public function getPaymentMachine(): PaymentMachine
    {
        return $this->container->getService($this->getPaymentFactoryName() . '.machine');
    }

    public function getPersonScheduleMachine(): PersonScheduleMachine
    {
        return $this->container->getService('transitions.personSchedule.machine');
    }

    public function getEmailMachine(): EmailMachine
    {
        return $this->container->getService('transitions.email.machine');
    }


    public function getPaymentFactoryName(): ?string
    {
        return 'transitions.fykosPayment';
    }

    /**
     * @throws NotImplementedException
     */
    public function getTeamMachine(EventModel $event): TeamMachine
    {
        switch ($event->event_type_id) {
            case 1:
                $machine = $this->container->getService('transitions.fof.machine');
                break;
            case 9:
                $machine = $this->container->getService('transitions.fol.machine');
                break;
            default:
                throw new NotImplementedException();
        }
        return $machine;
    }

    /**
     * @phpstan-return EventParticipantMachine|TeamMachine
     * @throws NotImplementedException
     */
    public function getEventMachine(EventModel $event): Machine
    {
        if ($event->isTeamEvent()) {
            return $this->getTeamMachine($event);
        } else {
            return $this->getParticipantMachine($event);
        }
    }
}
