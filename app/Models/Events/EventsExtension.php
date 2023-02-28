<?php

declare(strict_types=1);

namespace FKSDB\Models\Events;

use FKSDB\Models\Events\Exceptions\MachineDefinitionException;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Events\Model\Holder\Field;
use FKSDB\Models\Expressions\Helpers;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\Transitions\Machine\EventParticipantMachine;
use FKSDB\Models\Transitions\TransitionsExtension;
use Nette\DI\CompilerExtension;
use Nette\DI\Container;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\DI\Definitions\Statement;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

class EventsExtension extends CompilerExtension
{
    public function getConfigSchema(): Schema
    {
        return Expect::arrayOf(
            Expect::structure([
                'eventTypeIds' => Expect::listOf('int'),
                'eventYears' => Expect::listOf('int')->default(null),
                'formLayout' => Expect::string('application'),
                'machine' => Expect::structure([
                    'stateEnum' => Expect::string()->default(EventParticipantStatus::class),
                    'transitions' => Expect::arrayOf(
                        Expect::structure([
                            'condition' => Helpers::createBoolExpressionSchemaType(true)->default(true),
                            'label' => Helpers::createExpressionSchemaType(),
                            'afterExecute' => Expect::listOf(Helpers::createExpressionSchemaType()),
                            'beforeExecute' => Expect::listOf(Helpers::createExpressionSchemaType()),
                            'behaviorType' => Expect::string('secondary'),
                        ])->castTo('array'),
                        Expect::string()
                    ),

                ])->castTo('array'),
                'holder' => Expect::structure([
                    'modifiable' => Helpers::createBoolExpressionSchemaType(true)->default(true),
                    'fields' => Expect::arrayOf(
                        Expect::structure([
                            'label' => Helpers::createExpressionSchemaType(),
                            'description' => Helpers::createExpressionSchemaType()->default(null),
                            'required' => Helpers::createBoolExpressionSchemaType(false)->default(false),
                            'modifiable' => Helpers::createBoolExpressionSchemaType(true)->default(true),
                            'visible' => Helpers::createBoolExpressionSchemaType(true)->default(true),
                            'default' => Expect::mixed(),
                            'factory' => Helpers::createExpressionSchemaType()->default('@event.DBReflectionFactory'),
                        ])->castTo('array'),
                        Expect::string()
                    ),
                    'formAdjustments' => Expect::listOf(
                        Expect::mixed()->before(
                            fn($value) => Helpers::resolveMixedExpression($value)
                        )
                    ),
                    'processings' => Expect::listOf(
                        Expect::mixed()->before(
                            fn($value) => Helpers::resolveMixedExpression($value)
                        )
                    ),
                ])->castTo('array'),
            ])->castTo('array'),
            Expect::string()
        )->castTo('array');
    }

    /**
     * @throws MachineDefinitionException
     */
    public function loadConfiguration(): void
    {
        parent::loadConfiguration();
        $config = $this->getConfig();
        $eventDispatchFactory = $this->getContainerBuilder()
            ->addDefinition('event.dispatch')->setFactory(EventDispatchFactory::class);

        $eventDispatchFactory->addSetup(
            'setTemplateDir',
            [$this->getContainerBuilder()->parameters['events']['templateDir']]
        );
        foreach ($config as $definitionName => $definition) {
            $keys = $this->createAccessKeys($definition);
            $machine = $this->createMachineFactory($definitionName);
            $holder = $this->createHolderFactory($definitionName);
            $eventDispatchFactory->addSetup(
                'addEvent',
                [$keys, Container::getMethodName($holder->getName()), $machine->getName(), $definition['formLayout']]
            );
        }
    }

    private function createTransitionService(string $baseName, string $mask, array $definition): array
    {
        [$sources, $target] = TransitionsExtension::parseMask($mask, EventParticipantStatus::class);
        $factories = [];
        foreach ($sources as $source) {
            $factory = TransitionsExtension::createCommonTransition(
                $this,
                $this->getContainerBuilder(),
                $baseName,
                $source,
                $target,
                $definition
            );
            $factories[] = $factory;
        }
        return $factories;
    }

    private function createFieldService(array $fieldDefinition): ServiceDefinition
    {
        $field = $this->getContainerBuilder()
            ->addDefinition($this->prefix(uniqid('Field_')))
            ->setFactory(Field::class, [$fieldDefinition['0'], $fieldDefinition['label']]);
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
        $factoryName = $this->prefix('Machine_' . $eventName);
        $definition = $this->getConfig()[$eventName]['machine'];
        $factory = $this->getContainerBuilder()
            ->addDefinition($factoryName)
            ->setFactory(EventParticipantMachine::class);

        foreach ($definition['transitions'] as $mask => $transitionDef) {
            $transitions = $this->createTransitionService($factoryName, $mask, $transitionDef);
            foreach ($transitions as $transition) {
                $factory->addSetup('addTransition', [$transition]);
            }
        }
        return $factory;
    }

    /**
     * @throws MachineDefinitionException
     */
    private function createHolderFactory(string $eventName): ServiceDefinition
    {
        $factory = $this->getContainerBuilder()
            ->addDefinition($this->prefix('Holder_' . $eventName))
            ->setFactory(BaseHolder::class)
            ->addSetup('setModifiable', [$this->getConfig()[$eventName]['holder']['modifiable']]);
        foreach ($this->getConfig()[$eventName]['holder']['fields'] as $name => $fieldDef) {
            array_unshift($fieldDef, $name);
            $factory->addSetup('addField', [new Statement($this->createFieldService($fieldDef))]);
        }
        foreach ($this->getConfig()[$eventName]['holder']['processings'] as $processing) {
            $factory->addSetup('addProcessing', [$processing]);
        }

        foreach ($this->getConfig()[$eventName]['holder']['formAdjustments'] as $formAdjustment) {
            $factory->addSetup('addFormAdjustment', [$formAdjustment]);
        }
        return $factory;
    }
}
