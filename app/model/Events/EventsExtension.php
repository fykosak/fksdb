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
use FKSDB\Config\NeonSchemaException;
use FKSDB\Config\NeonScheme;
use Nette\DI\CompilerExtension;
use Nette\DI\Config\Loader;
use Nette\DI\Config\Helpers as ConfigHelpers;
use Nette\DI\Container;
use Nette\DI\ServiceDefinition;
use Nette\DI\Statement;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
use Nette\Utils\Arrays;
use Nette\Utils\Strings;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class EventsExtension extends CompilerExtension {

    const MAIN_RESOLVER = 'eventLayoutResolver';
    const FIELD_FACTORY = 'Field_';
    const MACHINE_PREFIX = 'Machine_';
    const HOLDER_PREFIX = 'Holder_';
    const BASE_MACHINE_PREFIX = 'BaseMachine_';
    const BASE_HOLDER_PREFIX = 'BaseHolder_';

    /** @const Maximum length of state identifier. */
    const STATE_SIZE = 20;

    /** @const Regexp for configuration section names */
    const NAME_PATTERN = '/[a-z0-9_]/i';
    /** @var string[] */
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
    /** @var */
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
    /** @var string */
    private $schemeFile;

    /**
     * EventsExtension constructor.
     * @param $schemaFile
     */
    public function __construct($schemaFile) {
        $this->schemeFile = $schemaFile;
        Helpers::registerSemantic(self::$semanticMap);
    }

    /**
     * Configuration loading
     * @throws NeonSchemaException
     */
    public function loadConfiguration() {
        parent::loadConfiguration();

        $this->loadScheme();

        $config = $this->getConfig();

        $eventDispatchFactory = $this->getContainerBuilder()
            ->addDefinition('event.dispatch')->setFactory(EventDispatchFactory::class);

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
            }
            $keys = $this->createAccessKeys($eventTypeIds, $definition);
            $this->createMachineFactory($definitionName, $definition);
            $this->createHolderFactory($definitionName, $definition);
            $holderName = $this->getHolderName($definitionName);
            $machineName = $this->getMachineName($definitionName);
            $holderMethodName = Container::getMethodName($holderName);
            $eventDispatchFactory->addSetup('addEvent', [$keys, $holderMethodName, $machineName]);
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

    private function createLayoutResolverFactory() {
        $def = $this->getContainerBuilder()->addDefinition(self::MAIN_RESOLVER);
        $def->setFactory(LayoutResolver::class);

        $parameters = $this->getContainerBuilder()->parameters;
        $templateDir = $parameters['events']['templateDir'];
        $def->setArguments([$templateDir, $this->definitionsMap]); //TODO!!
    }

    /**
     * @param string $baseName
     * @param array $states
     * @param string $mask
     * @param array $definition
     * @return ServiceDefinition
     */
    private function createTransitionService(string $baseName, array $states, string $mask, array $definition): ServiceDefinition {
        if (!Transition::validateTransition($mask, $states)) {
            throw new MachineDefinitionException("Invalid transition $mask for base machine $baseName.");
        }

        if (!$definition['label'] && $definition['visible'] !== false) {
            throw new MachineDefinitionException("Transition $mask with non-false visibility must have label defined.");
        }

        $factory = $this->getContainerBuilder()->addDefinition($this->getTransitionName($baseName, $mask));
        $factory->setFactory(Transition::class, [$mask, $definition['label'], $definition['behaviorType']]);
        $parameters = array_keys($this->scheme['transition']);
        foreach ($parameters as $parameter) {
            switch ($parameter) {
                case 'label':
                case 'onExecuted':
                case 'behaviorType':
                    break;
                default:
                    if (isset($definition[$parameter])) {
                        $factory->addSetup('set' . ucfirst($parameter), [$definition[$parameter]]);
                    }
            }
        }
        $factory->addSetup('setEvaluator', ['@events.expressionEvaluator']);
        $factory->addSetup('$service->onExecuted = array_merge($service->onExecuted, ?)', [$definition['onExecuted']]);
        return $factory;
    }

    /**
     * @param array $fieldDefinition
     * @return ServiceDefinition
     */
    private function createFieldService(array $fieldDefinition): ServiceDefinition {
        $field = $this->getContainerBuilder()
            ->addDefinition($this->getFieldName())
            ->setFactory(Field::class, [$fieldDefinition['0'], $fieldDefinition['label']]);

        $field->addSetup('setEvaluator', ['@events.expressionEvaluator']);
        foreach ($fieldDefinition as $key => $parameter) {
            switch ($key) {
                case 'name':
                case 'label':
                    break;
                default:
                    $field->addSetup('set' . ucfirst($key), [$parameter]);
            }
        }
        return $field;
    }

    /**
     * @param $eventTypeIds
     * @param $definition
     * @return array
     */
    private function createAccessKeys($eventTypeIds, $definition): array {
        $keys = [];
        foreach ($eventTypeIds as $eventTypeId) {
            if ($definition['eventYears'] === true) {
                $keys[] = (string)$eventTypeId;
            } else {
                foreach ($definition['eventYears'] as $year) {
                    $key = $eventTypeId . '-' . $year;
                    $keys[] = $key;
                }
            }
        }
        return $keys;
    }
    /*
     * Specialized machine factories
     */

    /**
     * @param $name
     * @param $definition
     * @return ServiceDefinition
     * @throws NeonSchemaException
     */
    private function createMachineFactory($name, $definition): ServiceDefinition {
        $machinesDef = NeonScheme::readSection($definition['machine'], $this->scheme['machine']);

        /*
         * Create factory definition.
         */
        $factoryName = $this->getMachineName($name);
        $factory = $this->getContainerBuilder()->addDefinition($factoryName);
        $factory->setFactory(Machine::class);
        /*
         * Create and add base machines into the machine (i.e. creating instances).
         */
        $primaryName = null;
        foreach ($machinesDef['baseMachines'] as $instanceName => $instanceDef) {
            $instanceDef = NeonScheme::readSection($instanceDef, $this->scheme['bmInstance']);
            if ($instanceDef['primary']) {
                if (!$primaryName) {
                    $primaryName = $instanceName;
                } else {
                    throw new MachineDefinitionException('Multiple primary machines defined.');
                }
            }
            $baseMachineFactory = $this->createBaseMachineFactory($name, $instanceDef['bmName'], $instanceName);
            $factory->addSetup('addBaseMachine', [$baseMachineFactory]);
        }
        if (!$primaryName) {
            throw new MachineDefinitionException('No primary machine defined.');
        }
        $factory->addSetup('setPrimaryMachine', [$primaryName]);


        /*
         * Set other attributes of the machine.
         */
        foreach (array_keys($machinesDef['baseMachines']) as $instanceName) {
            $joins = Arrays::get($machinesDef['joins'], $instanceName, []);

            foreach ($joins as $mask => $induced) {
                $factory->addSetup("\$service->getBaseMachine(?)->addInducedTransition(?, ?)", [$instanceName, $mask, $induced]);
            }
        }
        return $factory;
    }

    /**
     * @param $eventName
     * @param $baseName
     * @param $instanceName
     * @return ServiceDefinition
     * @throws NeonSchemaException
     */
    private function createBaseMachineFactory(string $eventName, string $baseName, string $instanceName): ServiceDefinition {
        $definition = $this->getBaseMachineConfig($eventName, $baseName);
        $factoryName = $this->getBaseMachineName($eventName, $baseName);
        $factory = $this->getContainerBuilder()->addDefinition(uniqid($factoryName));

        $factory->setFactory(BaseMachine::class, [$instanceName]);
        // no parameter must be set
        /*  $parameters = array_keys($this->scheme['bmInstance']);
          foreach ($parameters as $parameter) {
              switch ($parameter) {
                  case 'label':
                  case 'name':
                  case 'bmName':
                      break;
                  default:
                      $factory->addSetup('set' . ucfirst($parameter), $instanceDefinition[$parameter]);
              }
          }*/

        $definition = NeonScheme::readSection($definition, $this->scheme['baseMachine']);
        foreach ($definition['states'] as $state => $label) {
            if (strlen($state) > self::STATE_SIZE) {
                throw new MachineDefinitionException("State name '$state' is too long. Use " . self::STATE_SIZE . " characters at most.");
            }
            $factory->addSetup('addState', [$state, $label]);
        }
        $states = array_keys($definition['states']);

        foreach ($definition['transitions'] as $mask => $transitionRawDef) {
            $transitionDef = NeonScheme::readSection($transitionRawDef, $this->scheme['transition']);
            $factory->addSetup('addTransition', [$this->createTransitionService($factoryName, $states, $mask, $transitionDef)]);
        }

        return $factory;
    }

    /*
     * Specialized data factories
     */


    /**
     * @param $name
     * @param $definition
     * @throws NeonSchemaException
     */
    private function createHolderFactory($name, $definition) {
        $machineDef = NeonScheme::readSection($definition['machine'], $this->scheme['machine']);
        // Create factory definition.
        $factoryName = $this->getHolderName($name);
        $factory = $this->getContainerBuilder()->addDefinition($factoryName);
        $factory->setFactory(Holder::class);
        //  $factory->setParameters(['FKSDB\ORM\Models\ModelEvent event']);

        // Create and add base machines into the machine (i.e. creating instances).
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
            $baseHolderFactory = $this->createBaseHolderFactory($name, $instanceDef['bmName'], $instanceName, $instanceDef);
            $factory->addSetup('addBaseHolder', [new Statement($baseHolderFactory)]);
        }
        if (!$primaryName) {
            throw new MachineDefinitionException('No primary machine defined.');
        }
        $factory->addSetup('setPrimaryHolder', [$primaryName]);
        $factory->addSetup('setSecondaryModelStrategy', [$machineDef['secondaryModelStrategy']]);

        foreach ($machineDef['processings'] as $processing) {
            $factory->addSetup('addProcessing', [$processing]);
        }

        foreach ($machineDef['formAdjustments'] as $formAdjustment) {
            $factory->addSetup('addFormAdjustment', [$formAdjustment]);
        }
    }

    /**
     * @param string $eventName
     * @param string $baseName
     * @param string $instanceName
     * @param array $instanceDefinition
     * @return ServiceDefinition
     * @throws NeonSchemaException
     */
    private function createBaseHolderFactory(string $eventName, string $baseName, string $instanceName, array $instanceDefinition) {
        $definition = $this->getBaseMachineConfig($eventName, $baseName);
        $factoryName = $this->getBaseHolderName($eventName, $baseName);
        $factory = $this->getContainerBuilder()->addDefinition(uniqid($factoryName));
        $factory->setFactory(BaseHolder::class, [$instanceName]);

        $parameters = array_keys($this->scheme['bmInstance']);

        foreach ($parameters as $parameter) {
            switch ($parameter) {
                case 'modifiable':
                case 'visible':
                case 'label':
                case 'description':
                    $factory->addSetup('set' . ucfirst($parameter), [$instanceDefinition[$parameter]]);
                    break;
                default:
                    break;
            }
        }

        $definition = NeonScheme::readSection($definition, $this->scheme['baseMachine']);

        $factory->addSetup('setService', [$definition['service']]);
        $factory->addSetup('setJoinOn', [$definition['joinOn']]);
        $factory->addSetup('setJoinTo', [$definition['joinTo']]);
        $factory->addSetup('setPersonIds', [$definition['personIds']]); // must be set after setService
        $factory->addSetup('setEventId', [$definition['eventId']]); // must be set after setService
        $factory->addSetup('setEvaluator', ['@events.expressionEvaluator']);
        $factory->addSetup('setValidator', ['@events.dataValidator']);
        $factory->addSetup('setEventRelation', [$definition['eventRelation']]);

        $config = $this->getConfig();
        $paramScheme = isset($definition['paramScheme']) ? $definition['paramScheme'] : $config[$eventName]['paramScheme'];
        foreach (array_keys($paramScheme) as $paramKey) {
            $this->validateConfigName($paramKey);
        }
        $factory->addSetup('setParamScheme', [$paramScheme]);

        $hasNonDetermining = false;
        foreach ($definition['fields'] as $name => $fieldDef) {
            $fieldDef = NeonScheme::readSection($fieldDef, $this->scheme['field']);
            if ($fieldDef['determining']) {
                if ($fieldDef['required']) {
                    throw new MachineDefinitionException("Field '$name' cannot be both required and determining. Set required on the base holder.");
                }
                if ($hasNonDetermining) {
                    throw new MachineDefinitionException("Field '$name' cannot be preceded by non-determining fields. Reorder the fields.");
                }
            } else {
                $hasNonDetermining = true;
            }
            array_unshift($fieldDef, $name);
            $factory->addSetup('addField', [new Statement($this->createFieldService($fieldDef))]);
        }
        return $factory;
    }

    /* **************** Naming **************** */

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
     * @param string $baseName
     * @param string $mask
     * @return string
     */
    private function getTransitionName(string $baseName, string $mask): string {
        return $id = uniqid($baseName . '_transition_' . str_replace('-', '_', Strings::webalize($mask)) . '__');
    }

    /**
     * @return string
     */
    private function getFieldName() {
        return $this->prefix(uniqid(self::FIELD_FACTORY));
    }

}

/**
 * Class MachineDefinitionException
 * *
 */
class MachineDefinitionException extends InvalidStateException {

}
