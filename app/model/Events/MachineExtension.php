<?php

namespace Events;

use FKS\Config\Expressions\Helpers;
use Nette\Config\CompilerExtension;
use Nette\Config\Loader;
use Nette\DI\Container;
use Nette\DI\Statement;
use Nette\InvalidStateException;
use Nette\Utils\Arrays;
use Nette\Utils\PhpGenerator\ClassType;
use Nette\Utils\PhpGenerator\Method;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class MachineExtension extends CompilerExtension {

    const MAIN_FACTORY = 'eventMachine';
    const TRANSITION_FACTORY = 'Transition';
    const MACHINE_PREFIX = 'Machine_';
    const BASE_MACHINE_PREFIX = 'BaseMachine_';
    const CLASS_MACHINE = 'Events\Machine';
    const CLASS_BASE_MACHINE = 'Events\BaseMachine';
    const CLASS_TRANSITION = 'Events\Transition';

    private $scheme;

    /**
     * Global registry of available events definitions.
     * 
     * @var array[event_type_id] => definition[] where definition (name =>, years =>)
     */
    private $idMaps = array();
    private $transtionFactory;
    private $schemeFile;

    function __construct($schemaFile) {
        $this->schemeFile = $schemaFile;
    }

    public function loadConfiguration() {
        parent::loadConfiguration();

        $this->loadScheme();

        $config = $this->getConfig();

        $this->createDispatchFactory();
        $this->createTransitionFactory();

        foreach ($config as $definitionName => $definition) {
            $eventTypeId = $definition['event_type_id'];
            $years = isset($definition['eventYears']) ? $definition['eventYears'] : true;

            if (!isset($this->idMaps[$eventTypeId])) {
                $this->idMaps[$eventTypeId] = array();
            }
            $this->idMaps[$eventTypeId][] = array(
                'name' => $definitionName,
                'years' => $years,
            );

            /*
             * Create base machine factories.
             */
            $machines = array();
            foreach ($definition['baseMachines'] as $baseMachineName => $baseMachineDef) {
                $machines[$baseMachineName] = $this->createBaseMachineFactory($definitionName, $baseMachineName, $baseMachineDef);
            }

            $this->createMachineFactory($definitionName, $definition, $machines);
        }
    }

    private function loadScheme() {
        $loader = new Loader();
        $this->getContainerBuilder()->addDependency($this->schemeFile);
        $this->scheme = $loader->load($this->schemeFile);
    }

    public function afterCompile(ClassType $class) {
        $methodName = Container::getMethodName(self::MAIN_FACTORY, false);
        $method = $class->methods[$methodName];
        $this->createDispatchFactoryBody($method);
    }

    private function createDispatchFactoryBody(Method $method) {
        $method->setBody(NULL);
        $method->addBody('$eventTypeId = $eventType->getPrimary();');
        $method->addBody('switch($eventTypeId) {');
        foreach ($this->idMaps as $eventTypeId => $definitions) {
            $method->addBody('case ?:', array($eventTypeId));
            $universalDefinition = null;
            $indent = "\t";
            foreach ($definitions as $definition) {
                $machineName = $this->getMachineName($definition['name']);
                $methodName = Container::getMethodName($machineName, false);
                $body = "return \$this->$methodName();";
                if ($definition['years'] === true) {
                    $universalDefinition = $body;
                } else {
                    $method->addBody($indent . 'if(in_array($eventYear, ?)) ' . $body, array($definition['years']));
                }
            }
            if ($universalDefinition) {
                $method->addBody($indent . $universalDefinition);
            } else {
                $method->addBody($indent . 'throw new Nette\InvalidArgumentException("Undefined year $eventYear for event_type_id $eventTypeId");');
            }
            $method->addBody('break;');
        }
        $method->addBody('default:
                throw new Nette\InvalidArgumentException("Unknown event_type_id $eventTypeId");
            break;
        }');
    }

    private function createDispatchFactory() {
        $def = $this->getContainerBuilder()->addDefinition(self::MAIN_FACTORY);
        $def->setShared(false);
        $def->setClass(self::CLASS_MACHINE);
        $def->setParameters(array('eventType', 'eventYear'));
    }

    private function createTransitionFactory() {
        $factory = $this->getContainerBuilder()->addDefinition($this->getTransitionName());
        $factory->setShared(false);
        $factory->setClass(self::CLASS_BASE_MACHINE);
        $factory->setInternal(true);

        $parameters = array_keys($this->scheme['transition']);
        array_unshift($parameters, 'mask');
        $factory->setParameters($parameters);

        $this->transtionFactory = $factory;
    }

    private function createMachineFactory($name, $definition, $machines) {
        $machineDef = $this->readDefinition($definition['machine'], $this->scheme['machine']);

        /*
         * Create factory definition.
         */
        $factoryName = $this->getMachineName($name);
        $factory = $this->getContainerBuilder()->addDefinition($factoryName);
        $factory->setShared(false);
        $factory->setClass(self::CLASS_MACHINE);
        $factory->setInternal(true);

        /*
         * Create and add base machines into the machine (i.e. creating instances).
         */
        $primaryName = null;
        foreach ($machineDef['baseMachines'] as $instanceName => $instanceDef) {
            $instanceDef = $this->readDefinition($instanceDef, $this->scheme['bmInstance']);

            if ($instanceDef['primary']) {
                if (!$primaryName) {
                    $primaryName = $instanceName;
                } else {
                    throw new MachineDefinitionException('Multiple primary machines defined.');
                }
            }

            $defka = $machines[$instanceDef['bmName']];
            $stmt = new Statement($defka, $instanceDef);
            $factory->addSetup('addBaseMachine', $stmt);
        }
        if (!$primaryName) {
            throw new MachineDefinitionException('No primary machine defined.');
        }
        $factory->addSetup('setPrimaryMachine', $primaryName);


        /*
         * Set other attributes of the machine.
         */
        foreach (array_keys($machineDef['baseMachines']) as $instanceName) {
            $joins = Arrays::get($machineDef['joins'], $instanceName, array());

            foreach ($joins as $mask => $induced) {
                $factory->addSetup("\$service->getBaseMachine(?)->addInducedTransition(?, ?)", array($instanceName, $mask, $induced));
            }
        }

        foreach ($machineDef['onSubmit'] as $onSubmit) {
            $factory->addSetup('$service->onSubmit[] = ?', $onSubmit);
        }

        $factory->addSetup('setHandler', $machineDef['handler']); //TODO some default handler?
        $factory->addSetup('freeze');
    }

    private function createBaseMachineFactory($name, $baseName, $definition) {
        $factoryName = $this->getBaseMachineName($name, $baseName);
        $factory = $this->getContainerBuilder()->addDefinition($factoryName);
        $factory->setShared(false);
        $factory->setClass(self::CLASS_BASE_MACHINE);
        $factory->setInternal(true);
        $factory->setParameters(array_keys($this->scheme['bmInstance']));

        $definition = $this->readDefinition($definition, $this->scheme['baseMachine']);
        foreach ($definition['states'] as $state => $label) {
            $factory->addSetup('addState', $state, $label);
        }

        foreach ($definition['transitions'] as $mask => $transitionDef) {
            $transitionDef = $this->readDefinition($transitionDef, $this->scheme['transition']);

            array_unshift($transitionDef, $mask);
            $defka = $this->transtionFactory;
            $stmt = new Statement($defka, $transitionDef);
            $factory->addSetup('addTransition', $stmt);
        }

        return $factory;
    }

    private function getMachineName($name) {
        return $this->prefix(self::MACHINE_PREFIX . $name);
    }

    private function getBaseMachineName($name, $baseName) {
        return $this->prefix(self::BASE_MACHINE_PREFIX . $name . '_' . $baseName);
    }

    private function getTransitionName() {
        return $this->prefix(self::TRANSITION_FACTORY);
    }

    private function readDefinition($definition, $schemeFragment) {
        $result = array();
        foreach ($schemeFragment as $key => $metadata) {
            if ($metadata === null || !array_key_exists('default', $metadata)) {
                $result[$key] = Arrays::get($definition, $key);
                if ($metadata === null) {
                    continue;
                }
            } else {
                $result[$key] = Arrays::get($definition, $key, $metadata['default']);
            }

            $type = Arrays::get($metadata, 'type', 'neon');
            if ($type == 'expression') {
                $result[$key] = Helpers::statementFromExpression($result[$key]);
            }
        }
        $unknown = array_diff(array_keys($definition), array_keys($schemeFragment));
        if ($unknown) {
            throw new MachineDefinitionException('Unknown key(s): ' . implode(', ', $unknown) . '.');
        }
        return $result;
    }

}

class MachineDefinitionException extends InvalidStateException {
    
}

