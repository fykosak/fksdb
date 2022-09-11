<?php

declare(strict_types=1);

namespace FKSDB\Models\Events;

use FKSDB\Models\Events\Exceptions\MachineDefinitionException;
use FKSDB\Models\Transitions\Machine\EventParticipantMachine;
use FKSDB\Models\Events\Machine\Transition;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Events\Model\Holder\Field;
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
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\Transitions\TransitionsExtension;
use Nette\DI\Config\Loader;
use Nette\DI\CompilerExtension;
use Nette\DI\Container;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\DI\Definitions\Statement;
use Nette\InvalidArgumentException;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Tracy\Debugger;

/**
 * It's a f**** magic!
 */
class EventsExtension extends CompilerExtension
{
    public const FIELD_FACTORY = 'Field_';
    public const MACHINE_PREFIX = 'Machine_';
    public const HOLDER_PREFIX = 'Holder_';


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

    private string $schemeFile;

    public function __construct(string $schemaFile)
    {
        $this->schemeFile = $schemaFile;
        Helpers::registerSemantic(self::$semanticMap);
    }

    public function getConfigSchema(): Schema
    {
        $expressionType = Expect::anyOf(Expect::string(), Expect::type(Statement::class))->before(
            fn($value) => Helpers::statementFromExpression($value)
        );
        $boolExpressionType = fn(bool $default) => Expect::anyOf(
            Expect::bool($default),
            Expect::type(Statement::class)
        )->before(
            fn($value) => Helpers::statementFromExpression($value)
        );
        $translateExpressionType = Expect::anyOf(Expect::string(), Expect::type(Statement::class))->before(
            fn($value) => Helpers::translate($value)
        );

        return Expect::arrayOf(
            Expect::structure([
                'eventTypeIds' => Expect::listOf('int'),
                'eventYears' => Expect::listOf('int')->default(null),
                'formLayout' => Expect::string('application'),
                'paramScheme' => Expect::array([]),
                'baseMachine' => Expect::structure([
                    'transitions' => Expect::arrayOf(
                        Expect::structure([
                            'condition' => $boolExpressionType(true)->default(true),
                            'label' => $translateExpressionType,
                            'afterExecute' => Expect::listOf($expressionType),
                            'beforeExecute' => Expect::listOf($expressionType),
                            'behaviorType' => Expect::string('secondary'),
                            'visible' => $boolExpressionType(true)->default(true),
                        ])->castTo('array'),
                        Expect::string()
                    ),
                    'fields' => Expect::arrayOf(
                        Expect::structure([
                            'label' => $translateExpressionType,
                            'description' => $translateExpressionType->default(null),
                            'required' => $boolExpressionType(false)->default(false),
                            'modifiable' => $boolExpressionType(true)->default(true),
                            'visible' => $boolExpressionType(true)->default(true),
                            'default' => Expect::mixed(),
                            'factory' => $expressionType->default('@event.DBReflectionFactory'),
                        ])->castTo('array'),
                        Expect::string()
                    ),
                    'service' => Expect::string(EventParticipantService::class),
                ])->castTo('array'),
                'machine' => Expect::structure([
                    'baseMachine' => Expect::structure([
                        'label' => $translateExpressionType,
                        'modifiable' => $boolExpressionType(true)->default(true),
                    ])->castTo('array'),
                    'formAdjustments' => Expect::listOf(
                        Expect::mixed()->before(fn($value) => Helpers::statementFromExpression($value))
                    ),
                    'processings' => Expect::listOf(
                        Expect::mixed()->before(fn($value) => Helpers::statementFromExpression($value))
                    ),
                ])->castTo('array'),
            ])->castTo('array'),
            'string'
        )->castTo('array');
    }

    /**
     * @throws MachineDefinitionException
     */
    public function loadConfiguration(): void
    {
        parent::loadConfiguration();

        $this->loadScheme();

        $config = $this->getConfig();

        $eventDispatchFactory = $this->getContainerBuilder()
            ->addDefinition('event.dispatch')->setFactory(EventDispatchFactory::class);

        $eventDispatchFactory->addSetup(
            'setTemplateDir',
            [$this->getContainerBuilder()->parameters['events']['templateDir']]
        );
        foreach ($config as $definitionName => $definition) {
            $this->validateConfigName($definitionName);

            $keys = $this->createAccessKeys($definition);
            $machine = $this->createMachineFactory($definitionName);
            $holder = $this->createHolderFactory($definitionName, $definition);
            $eventDispatchFactory->addSetup(
                'addEvent',
                [$keys, Container::getMethodName($holder->getName()), $machine->getName(), $definition['formLayout']]
            );
        }
    }

    private function loadScheme(): void
    {
        $loader = new Loader();
        $this->getContainerBuilder()->addDependency($this->schemeFile);
        $this->scheme = $loader->load($this->schemeFile);
    }

    private function getBaseMachineConfig(string $eventName): array
    {
        return $this->getConfig()[$eventName]['baseMachine'];
    }

    private function validateConfigName(string $name): void
    {
        if (!preg_match(self::NAME_PATTERN, $name)) {
            throw new InvalidArgumentException("Section name '$name' in events configuration is invalid.");
        }
    }

    private function createTransitionService(string $baseName, string $mask, array $definition): array
    {
        [$sources, $target] = TransitionsExtension::parseMask($mask, EventParticipantStatus::class);
        $factories = [];
        foreach ($sources as $source) {
            if (!$definition['label'] && $definition['visible'] !== false) {
                throw new MachineDefinitionException(
                    "Transition $mask with non-false visibility must have label defined."
                );
            }
            $factory = TransitionsExtension::createCommonTransition(
                $this,
                $this->getContainerBuilder(),
                Transition::class,
                $baseName,
                $source,
                $target,
                $definition
            );
            $parameters = array_keys($this->scheme['transition']);
            foreach ($parameters as $parameter) {
                switch ($parameter) {
                    case 'label':
                    case 'afterExecute':
                    case 'beforeExecute':
                    case 'behaviorType':
                    case 'condition':
                        break;
                    default:
                        if (isset($definition[$parameter])) {
                            $factory->addSetup('set' . ucfirst($parameter), [$definition[$parameter]]);
                        }
                }
            }
            $factories[] = $factory;
        }
        return $factories;
    }

    private function createFieldService(array $fieldDefinition): ServiceDefinition
    {
        $field = $this->getContainerBuilder()
            ->addDefinition($this->getFieldName())
            ->setFactory(Field::class, [$fieldDefinition['0'], $fieldDefinition['label']])
            ->addSetup('setEvaluator', ['@events.expressionEvaluator']);
        foreach ($fieldDefinition as $key => $parameter) {
            if ($key == 'required') {
                Debugger::barDump($parameter);
            }
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
     * @param string[][] $definition
     * @return string[]
     */
    private function createAccessKeys(array $definition): array
    {
        $keys = [];
        foreach ($definition['eventTypeIds'] as $eventTypeId) {
            if (isset($definition['eventYears']) && $definition['eventYears'] !== true) {
                foreach ($definition['eventYears'] as $year) {
                    $key = $eventTypeId . '-' . $year;
                    $keys[] = $key;
                }
            } else {
                $keys[] = (string)$eventTypeId;
            }
        }
        return $keys;
    }

    /**
     * @throws MachineDefinitionException
     */
    private function createMachineFactory(string $eventName): ServiceDefinition
    {
        $factoryName = $this->getMachineName($eventName);
        $definition = $this->getBaseMachineConfig($eventName);
        $factory = $this->getContainerBuilder()->addDefinition($factoryName);

        $factory->setFactory(EventParticipantMachine::class);

        foreach ($definition['transitions'] as $mask => $transitionDef) {
            $transitions = $this->createTransitionService($factoryName, $mask, $transitionDef);
            foreach ($transitions as $transition) {
                $factory->addSetup(
                    'addTransition',
                    [$transition]
                );
            }
        }
        return $factory;
    }

    /*
     * Specialized data factories
     */

    /**
     * @throws MachineDefinitionException
     */
    private function createHolderFactory(string $eventName, array $definition): ServiceDefinition
    {
        $machineDef = $definition['machine'];

        $instanceDef = $machineDef['baseMachine'];
        $factoryName = $this->getHolderName($eventName);
        $definition = $this->getBaseMachineConfig($eventName);
        $factory = $this->getContainerBuilder()->addDefinition($factoryName);
        $factory->setFactory(BaseHolder::class);

        $parameters = array_keys($this->scheme['bmInstance']);

        foreach ($parameters as $parameter) {
            switch ($parameter) {
                case 'modifiable':
                case 'label':
                    $factory->addSetup('set' . ucfirst($parameter), [$instanceDef[$parameter]]);
                    break;
                default:
                    break;
            }
        }

        $factory->addSetup('setService', [$definition['service']]);
        $factory->addSetup('setEvaluator', ['@events.expressionEvaluator']);
        $factory->addSetup('setValidator', ['@events.dataValidator']);

        $config = $this->getConfig();
        $paramScheme = $definition['paramScheme'] ?? $config[$eventName]['paramScheme'];
        foreach (array_keys($paramScheme) as $paramKey) {
            $this->validateConfigName($paramKey);
        }
        $factory->addSetup('setParamScheme', [$paramScheme]);

        foreach ($definition['fields'] as $name => $fieldDef) {
            array_unshift($fieldDef, $name);
            $factory->addSetup('addField', [new Statement($this->createFieldService($fieldDef))]);
        }
        foreach ($machineDef['processings'] as $processing) {
            $factory->addSetup('addProcessing', [$processing]);
        }

        foreach ($machineDef['formAdjustments'] as $formAdjustment) {
            $factory->addSetup('addFormAdjustment', [$formAdjustment]);
        }
        return $factory;
    }

    /* **************** Naming **************** */

    private function getMachineName(string $name): string
    {
        return $this->prefix(self::MACHINE_PREFIX . $name);
    }

    private function getHolderName(string $name): string
    {
        return $this->prefix(self::HOLDER_PREFIX . $name);
    }

    private function getFieldName(): string
    {
        return $this->prefix(uniqid(self::FIELD_FACTORY));
    }
}
