<?php

namespace FKSDB\Events;

use Events\Model\Holder\BaseHolder;
use Events\Model\Holder\Holder;
use FKSDB\Config\NeonSchemaException;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Tracy\Debugger;

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
     * @param string $machineMethodName
     * @param string $holderMethodName
     */
    public function addEvent(array $key, string $machineMethodName, string $holderMethodName) {
        $this->definitions[] = ['keys' => $key, 'machineMethod' => $machineMethodName, 'holderMethod' => $holderMethodName];
    }

    /**
     * @param ModelEvent $event
     * @return mixed
     * @throws BadRequestException
     */
    public function getEventMachine(ModelEvent $event) {
        $definition = $this->findDefinition($event);
        Debugger::barDump($definition);
        return $this->container->{$definition['machineMethod']}($event);
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
     */
    public function getDummyHolder(ModelEvent $event): Holder {
        $definition = $this->findDefinition($event);
        return $this->container->{$definition['holderMethod']}($event);
    }

    /**
     * @param ModelEvent $event
     * @return string
     */
    private function createKey(ModelEvent $event): string {
        return $event->event_type_id . '-' . $event->event_year;
    }

}
