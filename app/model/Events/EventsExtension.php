<?php

namespace Events;

use Events\Machine\Transition;
use FKS\Config\NeonScheme;
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
class EventsExtension extends CompilerExtension {

    const MAIN_FACTORY = 'eventMachine';
    const MAIN_HOLDER = 'eventHolder';
    const MAIN_RESOLVER = 'eventLayoutResolver';
    const TRANSITION_FACTORY = 'Transition';
    const FIELD_FACTORY = 'Field';
    const MACHINE_PREFIX = 'Machine_';
    const HOLDER_PREFIX = 'Holder_';
    const BASE_MACHINE_PREFIX = 'BaseMachine_';
    const BASE_HOLDER_PREFIX = 'BaseHolder_';
    const CLASS_MACHINE = 'Events\Machine\Machine';
    const CLASS_BASE_MACHINE = 'Events\Machine\BaseMachine';
    const CLASS_TRANSITION = 'Events\Machine\Transition';
    const CLASS_FIELD = 'Events\Model\Holder\Field';
    const CLASS_BASE_HOLDER = 'Events\Model\Holder\BaseHolder';
    const CLASS_HOLDER = 'Events\Model\Holder\Holder';
    const CLASS_RESOLVER = 'FKSDB\Components\Grids\Events\LayoutResolver';

    /** @const Maximum length of state identifier. */
    const STATE_SIZE = 20;

    private $scheme;

    /**
     * Global registry of available events definitions.
     * 
     * @var array[event_type_id] => definition[] where definition (name =>, years =>)
     */
    private $idMaps = array();
    private $transtionFactory;
    private $fieldFactory;
    private $schemeFile;
    private $baseDefinitions = array('machines' => array(), 'holders' => array());

    function __construct($schemaFile) {
        $this->schemeFile = $schemaFile;
    }

    /*
     * Configuration loading
     */

    public function loadConfiguration() {
        parent::loadConfiguration();

        $this->loadScheme();

        $config = $this->getConfig();

        $this->createDispatchFactories();
        $this->createTransitionFactory();
        $this->createFieldFactory();

        foreach ($config as $definitionName => $definition) {
            $definition = NeonScheme::readSection($definition, $this->scheme['definition']);
            $eventTypeId = $definition['event_type_id'];

            if (!isset($this->idMaps[$eventTypeId])) {
                $this->idMaps[$eventTypeId] = array();
            }
            $this->idMaps[$eventTypeId][] = array(
                'name' => $definitionName,
                'years' => $definition['eventYears'],
                'tableLayout' => $definition['tableLayout'],
            );

            /*
             * Create base machine factories.
             */
            foreach ($definition['baseMachines'] as $baseName => $baseMachineDef) {
                $this->baseDefinitions['machines'][$baseName] = $this->createBaseMachineFactory($definitionName, $baseName, $baseMachineDef);
                $this->baseDefinitions['holders'][$baseName] = $this->createBaseHolderFactory($definitionName, $baseName, $baseMachineDef);
            }

            $this->createMachineFactory($definitionName, $definition);
            $this->createHolderFactory($definitionName, $definition);
        }

        $this->createLayoutResolverFactory();
    }

    private function loadScheme() {
        $loader = new Loader();
        $this->getContainerBuilder()->addDependency($this->schemeFile);
        $this->scheme = $loader->load($this->schemeFile);
    }

    /*
     * Dispatch factories
     */

    private function createDispatchFactoryBody(Method $method, $nameCallback) {
        $method->setBody(NULL);
        $method->addBody('$eventTypeId = $event->event_type_id;');
        $method->addBody('$eventYear = $event->event_year;');
        $method->addBody('switch($eventTypeId) {');
        foreach ($this->idMaps as $eventTypeId => $definitions) {
            $method->addBody('case ?:', array($eventTypeId));
            $universalDefinition = null;
            $indent = "\t";
            foreach ($definitions as $definition) {
                $machineName = $nameCallback($definition['name']);
                $methodName = Container::getMethodName($machineName, false);
                $body = "return \$this->$methodName(\$event);";
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

    private function createDispatchFactories() {
        $def = $this->getContainerBuilder()->addDefinition(self::MAIN_FACTORY);
        $def->setShared(false);
        $def->setClass(self::CLASS_MACHINE);
        $def->setParameters(array('ModelEvent event'));

        $def = $this->getContainerBuilder()->addDefinition(self::MAIN_HOLDER);
        $def->setShared(false);
        $def->setClass(self::CLASS_HOLDER);
        $def->setParameters(array('ModelEvent event'));
    }

    public function afterCompile(ClassType $class) {
        $methodName = Container::getMethodName(self::MAIN_FACTORY, false);
        $method = $class->methods[$methodName];
        $this->createDispatchFactoryBody($method, array($this, 'getMachineName'));

        $methodName = Container::getMethodName(self::MAIN_HOLDER, false);
        $method = $class->methods[$methodName];
        $this->createDispatchFactoryBody($method, array($this, 'getHolderName'));
    }

    private function createLayoutResolverFactory() {
        $def = $this->getContainerBuilder()->addDefinition(self::MAIN_RESOLVER);
        $def->setShared(true);
        $def->setClass(self::CLASS_RESOLVER);

        $parameters = $this->getContainerBuilder()->parameters;
        $templateDir = $parameters['events']['templateDir'];
        $def->setArguments(array($templateDir, $this->idMaps));
    }

    /*
     * Shared factories
     */

    private function createTransitionFactory() {
        $factory = $this->getContainerBuilder()->addDefinition($this->getTransitionName());
        $factory->setShared(false);
        $factory->setClass(self::CLASS_TRANSITION, array('%mask%', '%label%'));
        $factory->setInternal(true);

        $parameters = array_keys($this->scheme['transition']);
        array_unshift($parameters, 'mask');
        $factory->setParameters($parameters);

        $factory->addSetup('setCondition', '%condition%');
        $factory->addSetup('$service->onExecuted = array_merge($service->onExecuted, ?)', '%onExecuted%');

        $this->transtionFactory = $factory;
    }

    private function createFieldFactory() {
        $factory = $this->getContainerBuilder()->addDefinition($this->getFieldName());
        $factory->setShared(false);
        $factory->setClass(self::CLASS_FIELD, array('%name%', '%label%'));
        $factory->setInternal(true);

        $parameters = array_keys($this->scheme['field']);
        array_unshift($parameters, 'name');
        $factory->setParameters($parameters);


        foreach (Arrays::grep($parameters, "/^name|label$/", PREG_GREP_INVERT) as $parameter) {
            $factory->addSetup('set' . ucfirst($parameter), "%$parameter%");
        }

        $this->fieldFactory = $factory;
    }

    /*
     * Specialized machine factories
     */

    private function createMachineFactory($name, $definition) {
        $machineDef = NeonScheme::readSection($definition['machine'], $this->scheme['machine']);

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
            $instanceDef = NeonScheme::readSection($instanceDef, $this->scheme['bmInstance']);

            if ($instanceDef['primary']) {
                if (!$primaryName) {
                    $primaryName = $instanceName;
                } else {
                    throw new MachineDefinitionException('Multiple primary machines defined.');
                }
            }

            $defka = $this->baseDefinitions['machines'][$instanceDef['bmName']];
            $instanceDef['name'] = $instanceName;
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

        $factory->addSetup('freeze');
    }

    private function createBaseMachineFactory($name, $baseName, $definition) {
        $factoryName = $this->getBaseMachineName($name, $baseName);
        $factory = $this->getContainerBuilder()->addDefinition($factoryName);
        $factory->setShared(false);
        $factory->setClass(self::CLASS_BASE_MACHINE, array('%name%'));
        $factory->setInternal(true);

        $parameters = array_keys($this->scheme['bmInstance']);
        array_unshift($parameters, 'name');
        $factory->setParameters($parameters);

        $definition = NeonScheme::readSection($definition, $this->scheme['baseMachine']);
        foreach ($definition['states'] as $state => $label) {
            if (strlen($state) > self::STATE_SIZE) {
                throw new MachineDefinitionException("State name '$state' is too long. Use " . self::STATE_SIZE . " characters at most.");
            }
            $factory->addSetup('addState', $state, $label);
        }
        $states = array_keys($definition['states']);

        foreach ($definition['transitions'] as $mask => $transitionDef) {
            if (!Transition::validateTransition($mask, $states)) {
                throw new MachineDefinitionException("Invalid transition $mask for base machine $name.$baseName.");
            }
            $transitionDef = NeonScheme::readSection($transitionDef, $this->scheme['transition']);

            array_unshift($transitionDef, $mask);
            $defka = $this->transtionFactory;
            $stmt = new Statement($defka, $transitionDef);
            $factory->addSetup('addTransition', $stmt);
        }

        return $factory;
    }

    /*
     * Specialized data factories
     */

    private function createHolderFactory($name, $definition) {
        $machineDef = NeonScheme::readSection($definition['machine'], $this->scheme['machine']);

        /*
         * Create factory definition.
         */
        $factoryName = $this->getHolderName($name);
        $factory = $this->getContainerBuilder()->addDefinition($factoryName);
        $factory->setShared(false);
        $factory->setClass(self::CLASS_HOLDER);
        $factory->setInternal(true);
        $factory->setParameters(array('ModelEvent event'));

        /*
         * Create and add base machines into the machine (i.e. creating instances).
         */
        $primaryName = null;
        foreach ($machineDef['baseMachines'] as $instanceName => $instanceDef) {
            $instanceDef = NeonScheme::readSection($instanceDef, $this->scheme['bmInstance']);

            if ($instanceDef['primary']) {
                if (!$primaryName) {
                    $primaryName = $instanceName;
                } else {
                    throw new MachineDefinitionException('Multiple primary machines defined.');
                }
            }

            $defka = $this->baseDefinitions['holders'][$instanceDef['bmName']];
            $instanceDef['name'] = $instanceName;
            $stmt = new Statement($defka, $instanceDef);
            $factory->addSetup('addBaseHolder', $stmt);
        }
        if (!$primaryName) {
            throw new MachineDefinitionException('No primary machine defined.');
        }
        $factory->addSetup('setPrimaryHolder', $primaryName);
        $factory->addSetup('setParamScheme', array($definition['paramScheme']));
        
        $factory->addSetup('setEvent', '%event%');

        foreach ($machineDef['processings'] as $processing) {
            $factory->addSetup('addProcessing', $processing);
        }

        foreach ($machineDef['formAdjustments'] as $formAdjustment) {
            $factory->addSetup('addFormAdjustment', $formAdjustment);
        }



        $factory->addSetup('freeze');
    }

    private function createBaseHolderFactory($name, $baseName, $definition) {
        $factoryName = $this->getBaseHolderName($name, $baseName);
        $factory = $this->getContainerBuilder()->addDefinition($factoryName);
        $factory->setShared(false);
        $factory->setClass(self::CLASS_BASE_HOLDER, array('%name%'));
        $factory->setInternal(true);

        $parameters = array_keys($this->scheme['bmInstance']);
        array_unshift($parameters, 'name');
        $factory->setParameters($parameters);

        $definition = NeonScheme::readSection($definition, $this->scheme['baseMachine']);
        $factory->addSetup('setService', $definition['service']);
        $factory->addSetup('setJoinOn', $definition['joinOn']);
        $factory->addSetup('setPersonIds', array($definition['personIds'])); // must be set after setService
        $factory->addSetup('setEventId', array($definition['eventId'])); // must be set after setService

        foreach (Arrays::grep($parameters, '/^modifiable|visible|label|description$/') as $parameter) {
            $factory->addSetup('set' . ucfirst($parameter), "%$parameter%");
        }

        $hasNondetermining = false;
        foreach ($definition['fields'] as $name => $fieldDef) {
            $fieldDef = NeonScheme::readSection($fieldDef, $this->scheme['field']);

            if ($fieldDef['determining']) {
                if ($fieldDef['required']) {
                    throw new MachineDefinitionException("Field '$name' cannot be both required and determining. Set required on the base holder.");
                }
                if ($hasNondetermining) {
                    throw new MachineDefinitionException("Field '$name' cannot be preceded by non-determining fields. Reorder the fields.");
                }
                $fieldDef['required'] = '%required%';
            } else {
                $hasNondetermining = true;
            }


            array_unshift($fieldDef, $name);
            $defka = $this->fieldFactory;
            $stmt = new Statement($defka, $fieldDef);
            $factory->addSetup('addField', $stmt);
        }

        return $factory;
    }

    /*
     * Naming
     */

    private function getMachineName($name) {
        return $this->prefix(self::MACHINE_PREFIX . $name);
    }

    private function getHolderName($name) {
        return $this->prefix(self::HOLDER_PREFIX . $name);
    }

    private function getBaseMachineName($name, $baseName) {
        return $this->prefix(self::BASE_MACHINE_PREFIX . $name . '_' . $baseName);
    }

    private function getBaseHolderName($name, $baseName) {
        return $this->prefix(self::BASE_HOLDER_PREFIX . $name . '_' . $baseName);
    }

    private function getTransitionName() {
        return $this->prefix(self::TRANSITION_FACTORY);
    }

    private function getFieldName() {
        return $this->prefix(self::FIELD_FACTORY);
    }

}

class MachineDefinitionException extends InvalidStateException {
    
}

