<?php

declare(strict_types=1);

namespace FKSDB\Models\Events;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Transitions\Machine\EventParticipantMachine;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Events\Exceptions\ConfigurationNotFoundException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\Transitions\Machine\TeamMachine;
use Nette\DI\Container;
use Nette\DI\MissingServiceException;
use Nette\InvalidStateException;

class EventDispatchFactory
{
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
     */
    public function getEventMachine(EventModel $event): EventParticipantMachine
    {
        $definition = $this->findDefinition($event);
        return $this->container->getService($definition['machineName']);
    }

    /**
     * @throws BadTypeException
     */
    public function getTeamMachine(EventModel $event): TeamMachine
    {
        switch ($event->event_type_id) {
            case 1:
                $machine = $this->container->getService('fof.default.machine');
                break;
            case 9:
                $machine = $this->container->getService('fol.default.machine');
                break;
            default:
                throw new InvalidStateException();
        }
        if (!$machine instanceof TeamMachine) {
            throw new BadTypeException(TeamMachine::class, $machine);
        }
        return $machine;
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
