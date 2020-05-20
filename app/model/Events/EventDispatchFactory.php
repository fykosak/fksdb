<?php

namespace FKSDB\Events;

use FKSDB\Config\NeonSchemaException;
use FKSDB\Events\Model\Holder\Holder;
use FKSDB\Events\Machine\Machine;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Application\BadRequestException;
use Nette\DI\Container;

/**
 * Class EventDispatchFactory
 * @package Events
 */
class EventDispatchFactory {
    /** @var array */
    private $definitions = [];
    /** @var Container */
    private $container;

    /**
     * EventDispatchFactory constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        $this->container = $container;
    }

    /**
     * @param array $key
     * @param string $machineName
     * @param string $holderMethodName
     */
    public function addEvent(array $key, string $holderMethodName, string $machineName) {
        $this->definitions[] = [
            'keys' => $key,
            'holderMethod' => $holderMethodName,
            'machineName' => $machineName,
        ];
    }

    /**
     * @param ModelEvent $event
     * @return mixed
     * @throws BadRequestException
     * @throws \Exception
     */
    public function getEventMachine(ModelEvent $event): Machine {
        $definition = $this->findDefinition($event);
        return $this->container->getService($definition['machineName']);
    }

    /**
     * @param ModelEvent $event
     * @return string[]
     * @throws BadRequestException
     */
    private function findDefinition(ModelEvent $event): array {
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
        throw new BadRequestException();
    }

    /**
     * @param ModelEvent $event
     * @return Holder
     * @throws BadRequestException
     * @throws NeonSchemaException
     */
    public function getDummyHolder(ModelEvent $event): Holder {
        $definition = $this->findDefinition($event);
        /** @var Holder $holder */
        $holder = $this->container->{$definition['holderMethod']}();
        $holder->inferEvent($event);
        return $holder;
    }

    /**
     * @param ModelEvent $event
     * @return string
     */
    private function createKey(ModelEvent $event): string {
        return $event->event_type_id . '-' . $event->event_year;
    }
}
