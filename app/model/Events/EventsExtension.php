<?php

namespace FKSDB\Events;

use FKSDB\Events\Machine\BaseMachine;
use FKSDB\Events\Machine\Machine;
use FKSDB\Events\Machine\Transition;
use FKSDB\Events\Model\Holder\BaseHolder;
use FKSDB\Events\Model\Holder\Field;
use FKSDB\Events\Model\Holder\Holder;
use FKSDB\Events\Model\Holder\SameYearEvent;
use FKSDB\Events\Semantics\Count;
use FKSDB\Events\Semantics\EventWas;
use FKSDB\Events\Semantics\Parameter;
use FKSDB\Events\Semantics\RegOpen;
use FKSDB\Events\Semantics\Role;
use FKSDB\Events\Semantics\State;
use FKSDB\Components\Forms\Factories\Events\ArrayOptions;
use FKSDB\Components\Forms\Factories\Events\CheckboxFactory;
use FKSDB\Components\Forms\Factories\Events\ChooserFactory;
use FKSDB\Components\Forms\Factories\Events\PersonFactory;
use FKSDB\Components\Grids\Events\LayoutResolver;
use FKSDB\Config\Expressions\Helpers;
use FKSDB\Config\NeonScheme;
use Nette\Config\CompilerExtension;
use Nette\Config\Helpers as ConfigHelpers;
use Nette\Config\Loader;
use Nette\DI\Container;
use Nette\DI\Statement;
use Nette\InvalidArgumentException;
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
    const CLASS_MACHINE = Machine::class;
    const CLASS_BASE_MACHINE = BaseMachine::class;
    const CLASS_TRANSITION = Transition::class;
    const CLASS_FIELD = Field::class;
    const CLASS_BASE_HOLDER = BaseHolder::class;
    const CLASS_HOLDER = Holder::class;
    const CLASS_RESOLVER = LayoutResolver::class;

    /** @const Maximum length of state identifier. */
    const STATE_SIZE = 20;

    /** @const Regexp for configuration section names */
    const NAME_PATTERN = '/[a-z0-9_]/i';

    public static $semanticMap = [
        'RefPerson' => PersonFactory::class,
        'Chooser' => ChooserFactory::class,
        'Checkbox' => CheckboxFactory::class,
        'Options' => ArrayOptions::class,
        'role' => Role::class,
        'regOpen' => RegOpen::class,
        'eventWas' => EventWas::class,
        'state' => State::class,
        'param' => Parameter::class,
        'parameter' => Parameter::class,
        'count' => Count::class,
    ];
    private $scheme;

    /**
     * Global registry of available events definitions.
     *
     * @var array[definitionName] => definition[] where definition (eventTypes =>, years =>, tableLayout =>, formLayout =>)
     */
    private $definitionsMap = [];

    /**
     * @var array[baseMachineFullName] => expanded configuration
     */
    private $baseMachineConfig = [];
    private $transtionFactory;
    private $fieldFactory;
    private $schemeFile;
    private $baseDefinitions = ['machines' => [], 'holders' => []];

    /**
     * EventsExtension constructor.
     * @param $schemaFile
     */
    function __construct($schemaFile) {
        $this->schemeFile = $schemaFile;
        Helpers::registerSemantic(self::$semanticMap);
    }

    /*
     * Configuration loading
     */
    /**
     * @throws \FKSDB\Config\NeonSchemaException
     */
    public function loadConfiguration() {
        parent::loadConfiguration();

        $this->loadScheme();

        $config = $this->getConfig();

        $this->createDispatchFactories();
        $this->createTransitionFactory();
        $this->createFieldFactory();

        foreach ($config as $definitionName => $definition) {
            $this->validateConfigName($definitionName);
            $definition = NeonScheme::readSection($definition, $this->scheme['definition']);
            $eventTypeIds = is_array($definition['event_type_id']) ? $definition['event_type_id'] : [$definition['event_type_id']];

            $this->definitionsMap[$definitionName] = [
                'eventTypes' => $eventTypeIds,
                'years' => $definition['eventYears'],
                'tableLayout' => $definition['tableLayout'],
                'formLayout' => $definition['formLayout'],
            ];

            /*
             * Create base machine factories.
             */
            foreach ($definition['baseMachines'] as $baseName => $baseMachineDef) {
                $this->validateConfigName($baseName);
                $baseMachineDef = $this->getBaseMachineConfig($definitionName, $baseName);
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

    /**
     * @param $definitionName
     * @param $baseName
     * @return mixed
     */
    private function getBaseMachineConfig($definitionName, $baseName) {
        $key = "$definitionName.$baseName";
        while (!isset($this->baseMachineConfig[$key])) { // 'while' instead of 'if' so that 'break' can be used instead of return
            $config = $this->getConfig();
            $baseMachineDef = $config[$definitionName]['baseMachines'][$baseName];

            /*
             * Find prototype configuration
             */
            $prototype = Arrays::get($baseMachineDef, 'prototype', null);
            unset($baseMachineDef['prototype']);
            if (!$prototype) {
                $this->baseMachineConfig[$key] = $baseMachineDef;
                break;
            }
            list($protoDefinitionName, $protoBaseName) = explode('.', $prototype);
            if (!isset($config[$protoDefinitionName]) || !isset($config[$protoDefinitionName]['baseMachines'][$protoBaseName])) {
                throw new MachineDefinitionException("Prototype '$prototype' not found.");
            }

            /*
             * Use prototype to fill some of values
             */
            $protoConfig = $this->getBaseMachineConfig($protoDefinitionName, $protoBaseName);
            $eventTypeId = $config[$protoDefinitionName]['event_type_id'];
            $protoConfig['eventRelation'] = new Statement(SameYearEvent::class, [$eventTypeId]);
            $protoConfig['paramScheme'] = $config[$protoDefinitionName]['paramScheme'];
            $this->baseMachineConfig[$key] = ConfigHelpers::merge($baseMachineDef, $protoConfig);
            break;
        }
        return $this->baseMachineConfig[$key];
    }

    /**
     * @param $name
     */
    private function validateConfigName($name) {
        if (!preg_match(self::NAME_PATTERN, $name)) {
            throw new InvalidArgumentException("Section name '$name' in events configuration is invalid.");
        }
    }

    /*
     * Dispatch factories
     */

    /**
     * @param Method $method
     * @param $nameCallback
     */
    private function createDispatchFactoryBody(Method $method, $nameCallback) {
        $method->setBody(NULL);
        $method->addBody('$eventTypeId = $event->event_type_id;');
        $method->addBody('$eventYear = $event->event_year;');
        $method->addBody('$key = "$eventTypeId-$eventYear";');
        $method->addBody('switch($key) {');
        $definitions = $this->getTransposedDefinitions();
        $universals = [];
        $indent = "\t";
        foreach ($definitions as $definitionName => $keys) {
            $machineName = $nameCallback($definitionName);
            $methodName = Container::getMethodName($machineName, false);
            $resultStmt = "return \$this->$methodName(\$event);";
            $cases = false;
            foreach ($keys as $key) {
                if (strpos($key, '-') === false) {
                    $universals[$key] = $resultStmt;
                    continue;
                } else {
                    $cases = true;
                    $method->addBody('case ?:', [$key]);
                }
            }
            if ($cases) {
                $method->addBody($indent . $resultStmt);
                $method->addBody('break;');
            }
        }

        $method->addBody('default:');
        $method->addBody($indent . 'switch($eventTypeId) {');
        foreach ($universals as $key => $resultStmt) {
            $method->addBody($indent . 'case ?:', [$key]);
            $method->addBody($indent . $resultStmt);
            $method->addBody($indent . 'break;');
        }
        $method->addBody('default:');
        $method->addBody('throw new Events\UndeclaredEventException("Unknown event_type_id $eventTypeId for event year $eventYear.");');
        $method->addBody($indent . '}');
        $method->addBody('}');
    }

    /**
     * @return array
     */
    private function getTransposedDefinitions() {
        $result = [];
        foreach ($this->definitionsMap as $definitionName => $definition) {
            $result[$definitionName] = [];
            foreach ($definition['eventTypes'] as $eventType) {
                if ($definition['years'] === true) {
                    $key = "$eventType";
                    $result[$definitionName][] = $key;
                } else {
                    foreach ($definition['years'] as $year) {
                        $key = "$eventType-$year";
                        $result[$definitionName][] = $key;
                    }
                }
            }
        }
        return $result;
    }

    private function createDispatchFactories() {
        $def = $this->getContainerBuilder()->addDefinition(self::MAIN_FACTORY);
        $def->setShared(false);
        $def->setClass(self::CLASS_MACHINE);
        $def->setParameters(['FKSDB\ORM\Models\ModelEvent event']);

        $def = $this->getContainerBuilder()->addDefinition(self::MAIN_HOLDER);
        $def->setShared(false);
        $def->setClass(self::CLASS_HOLDER);
        $def->setParameters(['FKSDB\ORM\Models\ModelEvent event']);
    }

    /**
     * @param ClassType $class
     */
    public function afterCompile(ClassType $class) {
        $methodName = Container::getMethodName(self::MAIN_FACTORY, false);
        $method = $class->methods[$methodName];
        $this->createDispatchFactoryBody($method, [$this, 'getMachineName']);

        $methodName = Container::getMethodName(self::MAIN_HOLDER, false);
        $method = $class->methods[$methodName];
        $this->createDispatchFactoryBody($method, [$this, 'getHolderName']);
    }

    private function createLayoutResolverFactory() {
        $def = $this->getContainerBuilder()->addDefinition(self::MAIN_RESOLVER);
        $def->setShared(true);
        $def->setClass(self::CLASS_RESOLVER);

        $parameters = $this->getContainerBuilder()->parameters;
        $templateDir = $parameters['events']['templateDir'];
        $def->setArguments([$templateDir, $this->definitionsMap]); //TODO!!
    }

    /*
     * Shared factories
     */

    private function createTransitionFactory() {
        $factory = $this->getContainerBuilder()->addDefinition($this->getTransitionName());
        $factory->setShared(false);
        $factory->setClass(self::CLASS_TRANSITION, ['%mask%', '%label%']);
        $factory->setInternal(true);

        $parameters = array_keys($this->scheme['transition']);
        array_unshift($parameters, 'mask');
        $factory->setParameters($parameters);

        $factory->addSetup('setCondition', '%condition%');
        $factory->addSetup('setEvaluator', '@events.expressionEvaluator');
        $factory->addSetup('$service->onExecuted = array_merge($service->onExecuted, ?)', '%onExecuted%');
        $factory->addSetup('setDangerous', '%dangerous%');
        $factory->addSetup('setVisible', '%visible%');

        $this->transtionFactory = $factory;
    }

    private function createFieldFactory() {
        $factory = $this->getContainerBuilder()->addDefinition($this->getFieldName());
        $factory->setClass(self::CLASS_FIELD, ['%name%', '%label%']);

        $parameters = array_keys($this->scheme['field']);
        array_unshift($parameters, 'name');
        $factory->setParameters($parameters);
        $factory->addSetup('setEvaluator', '@events.expressionEvaluator');
        foreach ($parameters as $parameter) {
            switch ($parameter) {
                case 'name':
                case 'label':
                    break;
                default:
                    $factory->addSetup('set' . ucfirst($parameter), "%$parameter%");
            }
        }
        $this->fieldFactory = $factory;
    }

    /*
     * Specialized machine factories
     */

    /**
     * @param $name
     * @param $definition
     * @throws \FKSDB\Config\NeonSchemaException
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
            $joins = Arrays::get($machineDef['joins'], $instanceName, []);

            foreach ($joins as $mask => $induced) {
                $factory->addSetup("\$service->getBaseMachine(?)->addInducedTransition(?, ?)", [$instanceName, $mask, $induced]);
            }
        }
    }

    /**
     * @param $name
     * @param $baseName
     * @param $definition
     * @return \Nette\DI\ServiceDefinition
     * @throws \FKSDB\Config\NeonSchemaException
     */
    private function createBaseMachineFactory($name, $baseName, $definition) {
        $factoryName = $this->getBaseMachineName($name, $baseName);
        $factory = $this->getContainerBuilder()->addDefinition($factoryName);
        $factory->setShared(false);
        $factory->setClass(self::CLASS_BASE_MACHINE, ['%name%']);
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
            if (!$transitionDef['label'] && $transitionDef['visible'] !== false) {
                throw new MachineDefinitionException("Transition $mask with non-false visibility must have label defined.");
            }

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

    /**
     * @param $name
     * @param $definition
     * @throws \FKSDB\Config\NeonSchemaException
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
        $factory->setParameters(['FKSDB\ORM\Models\ModelEvent event']);

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
            $instanceDef['event'] = '%event%';
            $stmt = new Statement($defka, $instanceDef);
            $factory->addSetup('addBaseHolder', $stmt);
        }
        if (!$primaryName) {
            throw new MachineDefinitionException('No primary machine defined.');
        }
        $factory->addSetup('setPrimaryHolder', $primaryName);
        $factory->addSetup('setSecondaryModelStrategy', [$machineDef['secondaryModelStrategy']]);

        foreach ($machineDef['processings'] as $processing) {
            $factory->addSetup('addProcessing', $processing);
        }

        foreach ($machineDef['formAdjustments'] as $formAdjustment) {
            $factory->addSetup('addFormAdjustment', $formAdjustment);
        }
    }

    /**
     * @param $definitionName
     * @param $baseName
     * @param $definition
     * @return \Nette\DI\ServiceDefinition
     * @throws \FKSDB\Config\NeonSchemaException
     */
    private function createBaseHolderFactory($definitionName, $baseName, $definition) {
        $factoryName = $this->getBaseHolderName($definitionName, $baseName);
        $factory = $this->getContainerBuilder()->addDefinition($factoryName);
        $factory->setShared(false);
        $factory->setClass(self::CLASS_BASE_HOLDER, ['%name%']);
        $factory->setInternal(true);

        $parameters = array_keys($this->scheme['bmInstance']);
        array_unshift($parameters, 'event');
        array_unshift($parameters, 'name');
        $factory->setParameters($parameters);

        $definition = NeonScheme::readSection($definition, $this->scheme['baseMachine']);
        $factory->addSetup('setService', $definition['service']);
        $factory->addSetup('setJoinOn', $definition['joinOn']);
        $factory->addSetup('setJoinTo', $definition['joinTo']);
        $factory->addSetup('setPersonIds', [$definition['personIds']]); // must be set after setService
        $factory->addSetup('setEventId', [$definition['eventId']]); // must be set after setService
        $factory->addSetup('setEvaluator', '@events.expressionEvaluator');
        $factory->addSetup('setValidator', '@events.dataValidator');
        $factory->addSetup('setEventRelation', [$definition['eventRelation']]);

        $config = $this->getConfig();
        $paramScheme = isset($definition['paramScheme']) ? $definition['paramScheme'] : $config[$definitionName]['paramScheme'];
        foreach (array_keys($paramScheme) as $paramKey) {
            $this->validateConfigName($paramKey);
        }
        $factory->addSetup('setParamScheme', [$paramScheme]);


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

            $factory->addSetup('addField', new Statement($this->fieldFactory, $fieldDef));
        }

        $factory->addSetup('inferEvent', '%event%'); // must be after setParamScheme

        return $factory;
    }

    /*
     * Naming
     */

    /**
     * @param $name
     * @return string
     */
    private function getMachineName($name) {
        return $this->prefix(self::MACHINE_PREFIX . $name);
    }

    /**
     * @param $name
     * @return string
     */
    private function getHolderName($name) {
        return $this->prefix(self::HOLDER_PREFIX . $name);
    }

    /**
     * @param $name
     * @param $baseName
     * @return string
     */
    private function getBaseMachineName($name, $baseName) {
        return $this->prefix(self::BASE_MACHINE_PREFIX . $name . '_' . $baseName);
    }

    /**
     * @param $name
     * @param $baseName
     * @return string
     */
    private function getBaseHolderName($name, $baseName) {
        return $this->prefix(self::BASE_HOLDER_PREFIX . $name . '_' . $baseName);
    }

    /**
     * @return string
     */
    private function getTransitionName() {
        return $this->prefix(self::TRANSITION_FACTORY);
    }

    /**
     * @return string
     */
    private function getFieldName() {
        return $this->prefix(self::FIELD_FACTORY);
    }

}

/**
 * Class MachineDefinitionException
 * @package Events
 */
class MachineDefinitionException extends InvalidStateException {

}
