<?php

declare(strict_types=1);

namespace FKSDB\Models\Events;

use FKSDB\Models\Events\Exceptions\ConfigurationNotFoundException;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use FKSDB\Models\Transitions\Machine\EventParticipantMachine;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Transitions\Machine\PaymentMachine;
use FKSDB\Models\Transitions\Machine\PersonScheduleMachine;
use FKSDB\Models\Transitions\Machine\TeamMachine;
use Nette\DI\Container;
use Nette\DI\MissingServiceException;
use Nette\InvalidStateException;

/**
 * @phpstan-type TDefinition array{
 *      keys:string[],
 *      holderMethod:string,
 *      machineName:string,
 *      formLayout:string
 * }
 */
class EventDispatchFactory
{
    /**
     * @phpstan-var array<int,TDefinition>
     */
    private array $definitions = [];
    private Container $container;
    private string $templateDir;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function setTemplateDir(string $templateDir): void
    {
        $this->templateDir = $templateDir;
    }

    /**
     * @phpstan-param string[] $key
     */
    public function addEvent(array $key, string $holderMethodName, string $machineName, string $formLayout): void
    {
        $this->definitions[] = [
            'keys' => $key,
            'holderMethod' => $holderMethodName,
            'machineName' => $machineName,
            'formLayout' => $formLayout,
        ];
    }

    /**
     * @throws ConfigurationNotFoundException
     * @throws MissingServiceException
     * @phpstan-return EventParticipantMachine<ParticipantHolder>|EventParticipantMachine<BaseHolder>
     */
    public function getParticipantMachine(EventModel $event): EventParticipantMachine
    {
        switch ($event->event_type_id) {
            case 2:
            case 14:
                return $this->container->getService('transitions.dsef.machine'); //@phpstan-ignore-line
            case 11:
            case 12:
                return $this->container->getService('transitions.setkani.machine'); //@phpstan-ignore-line
        }
        $definition = $this->findDefinition($event);
        /** @var EventParticipantMachine<BaseHolder> $machine */
        $machine = $this->container->getService($definition['machineName']);
        return $machine;
    }

    public function getPaymentMachine(EventModel $event): PaymentMachine
    {
        return $this->container->getService($this->getPaymentFactoryName($event) . '.machine'); //@phpstan-ignore-line
    }

    public function getPersonScheduleMachine(): PersonScheduleMachine
    {
        return $this->container->getService('transitions.personSchedule.machine'); //@phpstan-ignore-line
    }

    public function getPaymentFactoryName(EventModel $event): ?string
    {
        if ($event->event_type_id === 1) {
            return sprintf('transitions.fyziklani%dpayment', $event->event_year);
        }
        return null;
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
     * @phpstan-return EventParticipantMachine<ParticipantHolder>|TeamMachine|EventParticipantMachine<BaseHolder>
     */
    public function getEventMachine(EventModel $event): Machine
    {
        if ($event->isTeamEvent()) {
            return $this->getTeamMachine($event);
        } else {
            return $this->getParticipantMachine($event);
        }
    }

    /**
     * @throws ConfigurationNotFoundException
     */
    public function getFormLayout(EventModel $event): string
    {
        $definition = $this->findDefinition($event);
        return $this->templateDir . DIRECTORY_SEPARATOR . $definition['formLayout'] . '.latte';
    }

    /**
     * @throws ConfigurationNotFoundException
     * @phpstan-return TDefinition
     */
    private function findDefinition(EventModel $event): array
    {
        $key = $this->createKey($event);
        foreach ($this->definitions as $definition) {
            if (in_array($key, $definition['keys'])) {
                return $definition;
            }
        }
        foreach ($this->definitions as $definition) {
            if (in_array((string)$event->event_type_id, $definition['keys'])) {
                return $definition;
            }
        }
        throw new ConfigurationNotFoundException($event);
    }

    /**
     * @throws ConfigurationNotFoundException
     */
    public function getDummyHolder(EventModel $event): BaseHolder
    {
        $definition = $this->findDefinition($event);
        /** @var BaseHolder $holder */
        $holder = $this->container->{$definition['holderMethod']}();
        $holder->setEvent($event);
        return $holder;
    }

    private function createKey(EventModel $event): string
    {
        return $event->event_type_id . '-' . $event->event_year;
    }
}
