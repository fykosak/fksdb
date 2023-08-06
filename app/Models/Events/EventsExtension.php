<?php

declare(strict_types=1);

namespace FKSDB\Models\Events;

use FKSDB\Models\Events\Exceptions\MachineDefinitionException;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Events\Model\Holder\Field;
use FKSDB\Models\Expressions\Helpers;
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
                'eventTypeIds' => Expect::listOf(Expect::int()),
                'eventYears' => Expect::listOf(Expect::int())->default(null),
                'formLayout' => Expect::string('application'),
                'machine' => TransitionsExtension::getMachineSchema(),
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
        $eventDispatchFactory = $this->getContainerBuilder()
            ->addDefinition('event.dispatch')
            ->setFactory(EventDispatchFactory::class);

        $eventDispatchFactory->addSetup(
            'setTemplateDir',
            [$this->getContainerBuilder()->parameters['events']['templateDir']]
        );
        foreach ($this->getConfig() as $definitionName => $definition) {//@phpstan-ignore-line
            $keys = $this->createAccessKeys($definition);
            $machine = TransitionsExtension::createMachine(
                $this,
                $definitionName,
                $this->getConfig()[$definitionName]['machine']//@phpstan-ignore-line
            );
            $holder = $this->createHolderFactory($definitionName);
            $eventDispatchFactory->addSetup(
                'addEvent',
                [$keys, Container::getMethodName($holder->getName()), $machine->getName(), $definition['formLayout']]
            );
        }
    }

    /**
     * @param array{
     *  label:string,
     *  description?:string,
     *  required:(callable():bool)|bool,
     *  modifiable:(callable():bool)|bool,
     *  visible:(callable():bool)|bool,
     *  default:mixed,
     *  factory:string,
     * } $fieldDefinition
     */
    private function createFieldService(string $name, array $fieldDefinition): ServiceDefinition
    {
        $field = $this->getContainerBuilder()
            ->addDefinition($this->prefix(uniqid('Field_')))
            ->setFactory(Field::class, [$name, $fieldDefinition['label']]);
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
    private function createHolderFactory(string $eventName): ServiceDefinition
    {
        $factory = $this->getContainerBuilder()
            ->addDefinition($this->prefix($eventName . '.holder'))
            ->setFactory(BaseHolder::class)
            /** @phpstan-ignore-next-line */
            ->addSetup('setModifiable', [$this->getConfig()[$eventName]['holder']['modifiable']]);
        /** @phpstan-ignore-next-line */
        foreach ($this->getConfig()[$eventName]['holder']['fields'] as $name => $fieldDef) {
            $factory->addSetup('addField', [new Statement($this->createFieldService($name, $fieldDef))]);
        }
        /** @phpstan-ignore-next-line */
        foreach ($this->getConfig()[$eventName]['holder']['processings'] as $processing) {
            $factory->addSetup('addProcessing', [$processing]);
        }
        /** @phpstan-ignore-next-line */
        foreach ($this->getConfig()[$eventName]['holder']['formAdjustments'] as $formAdjustment) {
            $factory->addSetup('addFormAdjustment', [$formAdjustment]);
        }
        return $factory;
    }
}
