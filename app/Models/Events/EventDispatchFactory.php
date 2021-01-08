<?php

namespace FKSDB\Models\Events;

use FKSDB\Models\Expressions\NeonSchemaException;
use FKSDB\Models\Events\Exceptions\ConfigurationNotFoundException;
use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\Events\Machine\Machine;
use FKSDB\Models\ORM\Models\ModelEvent;
use Nette\DI\Container;
use Nette\DI\MissingServiceException;

/**
 * Class EventDispatchFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
class EventDispatchFactory {

    private array $definitions = [];

    private Container $container;

    private string $templateDir;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    public function setTemplateDir(string $templateDir): void {
        $this->templateDir = $templateDir;
    }

    public function addEvent(array $key, string $holderMethodName, string $machineName, string $formLayout): void {
        $this->definitions[] = [
            'keys' => $key,
            'holderMethod' => $holderMethodName,
            'machineName' => $machineName,
            'formLayout' => $formLayout,
        ];
    }

    /**
     * @param ModelEvent $event
     * @return Machine
     * @throws ConfigurationNotFoundException
     * @throws MissingServiceException
     */
    public function getEventMachine(ModelEvent $event): Machine {
        $definition = $this->findDefinition($event);
        return $this->container->getService($definition['machineName']);
    }

    /**
     * @param ModelEvent $event
     * @return string
     * @throws ConfigurationNotFoundException
     */
    public function getFormLayout(ModelEvent $event): string {
        $definition = $this->findDefinition($event);
        return $this->templateDir . DIRECTORY_SEPARATOR . $definition['formLayout'] . '.latte';
    }

    /**
     * @param ModelEvent $event
     * @return array
     * @throws ConfigurationNotFoundException
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
        throw new ConfigurationNotFoundException($event);
    }

    /**
     * @param ModelEvent $event
     * @return Holder
     * @throws ConfigurationNotFoundException
     * @throws NeonSchemaException
     */
    public function getDummyHolder(ModelEvent $event): Holder {
        $definition = $this->findDefinition($event);
        /** @var Holder $holder */
        $holder = $this->container->{$definition['holderMethod']}();
        $holder->inferEvent($event);
        return $holder;
    }

    private function createKey(ModelEvent $event): string {
        return $event->event_type_id . '-' . $event->event_year;
    }
}
