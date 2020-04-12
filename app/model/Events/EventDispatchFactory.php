<?php

namespace FKSDB\Events;

use Events\Model\Holder\BaseHolder;
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
    private $definitions = [];
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
        $eventTypeId = $event->event_type_id;
        $eventYear = $event->event_year;
        $key = "$eventTypeId-$eventYear";
        foreach ($this->definitions as $definition) {
            if (in_array($key, $definition['keys'])) {
                return $this->container->{$definition['machineMethod']}($event);
            }
        }
        throw new BadRequestException();
    }

    /**
     * @param ModelEvent $event
     * @return mixed
     * @throws BadRequestException
     * @throws NeonSchemaException
     */
    public function getEventHolder(ModelEvent $event): BaseHolder {
        Debugger::barDump($this);
        $eventTypeId = $event->event_type_id;
        $eventYear = $event->event_year;
        $key = "$eventTypeId-$eventYear";
        foreach ($this->definitions as $definition) {
            if (in_array($key, $definition['keys'])) {
                /** @var BaseHolder $baseHolder */
                $baseHolder = $this->container->{$definition['holderMethod']}($event);
                $baseHolder->inferEvent($event);
            }
        }
        throw new BadRequestException();
    }

}
