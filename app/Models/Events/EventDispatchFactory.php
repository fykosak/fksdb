<?php

declare(strict_types=1);

namespace FKSDB\Models\Events;

use FKSDB\Models\Events\Exceptions\ConfigurationNotFoundException;
use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\Transitions\Machine\EventParticipantMachine;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Transitions\Machine\PaymentMachine;
use FKSDB\Models\Transitions\Machine\PersonScheduleMachine;
use FKSDB\Models\Transitions\Machine\TeamMachine;
use Nette\DI\Container;
use Nette\DI\MissingServiceException;
use Nette\InvalidStateException;

class EventDispatchFactory
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @throws ConfigurationNotFoundException
     * @throws MissingServiceException
     * @throws NotImplementedException
     */
    public function getParticipantMachine(EventModel $event): EventParticipantMachine
    {
        switch ($event->event_type_id) {
            case 4:
            case 5:
                return $this->container->getService('transitions.sous.machine'); //@phpstan-ignore-line
            case 2:
            case 14:
                return $this->container->getService('transitions.dsef.machine'); //@phpstan-ignore-line
            case 10:
                return $this->container->getService('transitions.tabor.machine'); //@phpstan-ignore-line
            case 11:
            case 12:
                return $this->container->getService('transitions.setkani.machine'); //@phpstan-ignore-line
            default:
                throw new NotImplementedException();
        }
    }

    public function getPaymentMachine(): PaymentMachine
    {
        return $this->container->getService($this->getPaymentFactoryName() . '.machine'); //@phpstan-ignore-line
    }

    public function getPersonScheduleMachine(): PersonScheduleMachine
    {
        return $this->container->getService('transitions.personSchedule.machine'); //@phpstan-ignore-line
    }

    public function getPaymentFactoryName(): ?string
    {
        return 'transitions.fykosPayment';
    }

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
                throw new InvalidStateException();
        }
        return $machine; //@phpstan-ignore-line
    }

    /**
     * @phpstan-return EventParticipantMachine|TeamMachine
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
