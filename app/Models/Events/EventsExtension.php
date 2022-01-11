<?php

namespace FKSDB\Models\Events;

use FKSDB\Models\Events\Exceptions\MachineDefinitionException;
use FKSDB\Models\Events\Machine\BaseMachine;
use FKSDB\Models\Events\Machine\Machine;
use FKSDB\Models\Events\Machine\Transition;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Events\Model\Holder\Field;
use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\Events\Model\Holder\SameYearEvent;
use FKSDB\Models\Events\Semantics\Count;
use FKSDB\Models\Events\Semantics\EventWas;
use FKSDB\Models\Events\Semantics\Parameter;
use FKSDB\Models\Events\Semantics\RegOpen;
use FKSDB\Models\Events\Semantics\Role;
use FKSDB\Models\Events\Semantics\State;
use FKSDB\Components\Forms\Factories\Events\ArrayOptions;
use FKSDB\Components\Forms\Factories\Events\CheckboxFactory;
use FKSDB\Components\Forms\Factories\Events\ChooserFactory;
use FKSDB\Components\Forms\Factories\Events\PersonFactory;
use FKSDB\Models\Expressions\Helpers;
use FKSDB\Models\Expressions\NeonSchemaException;
use FKSDB\Models\Expressions\NeonScheme;
use Nette\DI\Config\Loader;
use Nette\DI\CompilerExtension;
use Nette\DI\Container;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\DI\Definitions\Statement;
use Nette\InvalidArgumentException;
use Nette\Utils\Strings;

/**
 * It's a f**** magic!
 */
class EventsExtension extends CompilerExtension {

    public const FIELD_FACTORY = 'Field_';
    public const MACHINE_PREFIX = 'Machine_';
    public const HOLDER_PREFIX = 'Holder_';
    public const BASE_MACHINE_PREFIX = 'BaseMachine_';
    public const BASE_HOLDER_PREFIX = 'BaseHolder_';

    /** @const Maximum length of state identifier. */
    public const STATE_SIZE = 20;

    /** @const Regexp for configuration section names */
    public const NAME_PATTERN = '/[a-z0-9_]/i';

    public static array $semanticMap = [
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

    private array $scheme;

    /** @var array[baseMachineFullName] => expanded configuration */
    private array $baseMachineConfig = [];

    private string $schemeFile;

    public function __construct(string $schemaFile) {
        $this->schemeFile = $schemaFile;
        Helpers::registerSemantic(self::$semanticMap);
    }

    /**
     * @throws MachineDefinitionException
     * @throws NeonSchemaException
     */
    public function loadConfiguration(): void {
        parent::loadConfiguration();

        $this->loadScheme();

        $config = $this->getConfig();

        $eventDispatchFactory = $this->getContainerBuilder()
            ->addDefinition('event.dispatch')->setFactory(EventDispatchFactory::class);

        $eventDispatchFactory->addSetup('setTemplateDir', [$this->getContainerBuilder()->parameters['events']['templateDir']]);
        foreach ($config as $definitionName => $definition) {
            $this->validateConfigName($definitionName);
            $definition = NeonScheme::readSection($definition, $this->scheme['definition']);
            $eventTypeIds = is_array($definition['event_type_id']) ? $definition['event_type_id'] : [$definition['event_type_id']];
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
            $eventDispatchFactory->addSetup('addEvent', [$keys, $holderMethodName, $machineName, $definition['formLayout']]);
        }
    }

    private function loadScheme(): void {
        $loader = new Loader();
        $this->getContainerBuilder()->addDependency($this->schemeFile);
        $this->scheme = $loader->load($this->schemeFile);
    }

    private function getBaseMachineConfig(string $definitionName, string $baseName): array {
        $key = "$definitionName.$baseName";
        while (!isset($this->baseMachineConfig[$key])) { // 'while' instead of 'if' so that 'break' can be used instead of return
            $config = $this->getConfig();
            $baseMachineDef = $config[$definitionName]['baseMachines'][$baseName];

            /*
             * Find prototype configuration
             */
            $prototype = $baseMachineDef['prototype'] ?? null;
            unset($baseMachineDef['prototype']);
            if (!$prototype) {
                $this->baseMachineConfig[$key] = $baseMachineDef;
                break;
            }
            [$protoDefinitionName, $protoBaseName] = explode('.', $prototype);
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
            $this->baseMachineConfig[$key] = \Nette\Schema\Helpers::merge($baseMachineDef, $protoConfig);
            break;
        }
        return $this->baseMachineConfig[$key];
    }

    private function validateConfigName(string $name): void {
        if (!preg_match(self::NAME_PATTERN, $name)) {
            throw new InvalidArgumentException("Section name '$name' in events configuration is invalid.");
        }
    }

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
        foreach ($definition['onExecuted'] as $cb) {
            $factory->addSetup('addAfterExecute', [$cb]);
        }

        return $factory;
    }

    private function createFieldService(array $fieldDefinition): ServiceDefinition {
        $field = $this->getContainerBuilder()
            ->addDefinition($this->getFieldName())
            ->setFactory(Field::class, [$fieldDefinition['0'], $fieldDefinition['label']]);

        $field->addSetup('setEvaluator', ['@events.expressionEvaluator']);
        foreach ($fieldDefinition as $key => $parameter) {
            if (is_numeric($key)) {
                continue;
            }
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
     * @param int[] $eventTypeIds
     * @param string[][] $definition
     * @return string[]
     */
    private function createAccessKeys(array $eventTypeIds, array $definition): array {
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
     * @throws MachineDefinitionException
     * @throws NeonSchemaException
     */
    private function createMachineFactory(string $name, array $definition): ServiceDefinition {
        $machinesDef = NeonScheme::readSection($definition['machine'], $this->scheme['machine']);
        // Create factory definition.
        $factoryName = $this->getMachineName($name);
        $factory = $this->getContainerBuilder()->addDefinition($factoryName);
        $factory->setFactory(Machine::class);

        // Create and add base machines into the machine (i.e. creating instances).
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
            $joins = $machinesDef['joins'][$instanceName] ?? [];

            foreach ($joins as $mask => $induced) {
                $factory->addSetup("\$service->getBaseMachine(?)->addInducedTransition(?, ?)", [$instanceName, $mask, $induced]);
            }
        }
        return $factory;
    }

    /**
     * @throws MachineDefinitionException
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
        foreach ($definition['states'] as $state) {
            if (strlen($state) > self::STATE_SIZE) {
                throw new MachineDefinitionException("State name '$state' is too long. Use " . self::STATE_SIZE . ' characters at most.');
            }
            $factory->addSetup('addState', [$state]);
        }

        foreach ($definition['transitions'] as $mask => $transitionRawDef) {
            $transitionDef = NeonScheme::readSection($transitionRawDef, $this->scheme['transition']);
            $factory->addSetup('addTransition', [$this->createTransitionService($factoryName, $definition['states'], $mask, $transitionDef)]);
        }

        return $factory;
    }

    /*
     * Specialized data factories
     */

    /**
     * @throws MachineDefinitionException
     * @throws NeonSchemaException
     */
    private function createHolderFactory(string $name, array $definition): void {
        $machineDef = NeonScheme::readSection($definition['machine'], $this->scheme['machine']);
        // Create factory definition.
        $factoryName = $this->getHolderName($name);
        $factory = $this->getContainerBuilder()->addDefinition($factoryName);
        $factory->setFactory(Holder::class);

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
     * @throws MachineDefinitionException
     * @throws NeonSchemaException
     */
    private function createBaseHolderFactory(string $eventName, string $baseName, string $instanceName, array $instanceDefinition): ServiceDefinition {
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
        $factory->addSetup('setEventIdColumn', [$definition['eventId']]); // must be set after setService
        $factory->addSetup('setEvaluator', ['@events.expressionEvaluator']);
        $factory->addSetup('setValidator', ['@events.dataValidator']);
        $factory->addSetup('setEventRelation', [$definition['eventRelation']]);

        $config = $this->getConfig();
        $paramScheme = $definition['paramScheme'] ?? $config[$eventName]['paramScheme'];
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
                $fieldDef['required'] = $instanceDefinition['required'];
            } else {
                $hasNonDetermining = true;
            }
            array_unshift($fieldDef, $name);
            $factory->addSetup('addField', [new Statement($this->createFieldService($fieldDef))]);
        }
        return $factory;
    }

    /* **************** Naming **************** */

    private function getMachineName(string $name): string {
        return $this->prefix(self::MACHINE_PREFIX . $name);
    }

    private function getHolderName(string $name): string {
        return $this->prefix(self::HOLDER_PREFIX . $name);
    }

    private function getBaseMachineName(string $name, string $baseName): string {
        return $this->prefix(self::BASE_MACHINE_PREFIX . $name . '_' . $baseName);
    }

    private function getBaseHolderName(string $name, string $baseName): string {
        return $this->prefix(self::BASE_HOLDER_PREFIX . $name . '_' . $baseName);
    }

    private function getTransitionName(string $baseName, string $mask): string {
        return uniqid($baseName . '_transition_' . str_replace('-', '_', Strings::webalize($mask)) . '__');
    }

    private function getFieldName(): string {
        return $this->prefix(uniqid(self::FIELD_FACTORY));
    }
}
