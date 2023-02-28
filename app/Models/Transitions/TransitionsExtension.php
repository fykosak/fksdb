<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions;

use FKSDB\Models\Expressions\Helpers;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Transitions\Transition\BehaviorType;
use FKSDB\Models\Transitions\Transition\Transition;
use Nette\DI\CompilerExtension;
use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

class TransitionsExtension extends CompilerExtension
{
    public function getConfigSchema(): Schema
    {
        return Expect::arrayOf(
            Expect::structure([
                'machine' => Expect::string(),
                'stateEnum' => Expect::string(),
                'decorator' => Expect::type(\Nette\DI\Definitions\Statement::class)->nullable(),
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
            Expect::string()
        );
    }

    public function loadConfiguration(): void
    {
        parent::loadConfiguration();
        $config = $this->getConfig();
        foreach ($config as $machineName => $machine) {
            $machineDefinition = $this->setUpMachine($machineName, $machine);
            $enumClassName = $machine['stateEnum'];
            foreach ($machine['transitions'] as $mask => $transition) {
                [$sources, $target] = self::parseMask($mask, $enumClassName);
                foreach ($sources as $source) {
                    $machineDefinition->addSetup('addTransition', [
                        self::createCommonTransition(
                            $this,
                            $this->getContainerBuilder(),
                            $machineName,
                            $source,
                            $target,
                            $transition
                        ),
                    ]);
                }
            }
        }
    }
    public static function createCommonTransition(
        CompilerExtension $extension,
        ContainerBuilder $builder,
        string $machineName,
        EnumColumn $source,
        EnumColumn $target,
        array $baseConfig
    ): ServiceDefinition {
        $factory = $builder->addDefinition(
            $extension->prefix(
                $machineName . '.' .
                ($source->value) . '.' .
                ($target->value)
            )
        )
            ->addTag($machineName)
            ->setType(Transition::class)
            ->addSetup('setCondition', [$baseConfig['condition'] ?? null])
            ->addSetup('setSourceStateEnum', [$source])
            ->addSetup('setTargetStateEnum', [$target])
            ->addSetup('setLabel', [Helpers::resolveMixedExpression($baseConfig['label'])])
            ->addSetup(
                'setBehaviorType',
                [
                    BehaviorType::tryFrom($baseConfig['behaviorType'] ?? 'secondary'),
                ]
            );
        if (isset($baseConfig['afterExecute'])) {
            foreach ($baseConfig['afterExecute'] as $callback) {
                $factory->addSetup('addAfterExecute', [$callback]);
            }
        }
        if (isset($baseConfig['beforeExecute'])) {
            foreach ($baseConfig['beforeExecute'] as $callback) {
                $factory->addSetup('addBeforeExecute', [$callback]);
            }
        }
        return $factory;
    }

    private function setUpMachine(string $machineName, array $machineConfig): ServiceDefinition
    {
        $builder = $this->getContainerBuilder();
        $machineDefinition = $builder->addDefinition($machineName . '.machine')->setFactory($machineConfig['machine']);
        if (isset($machineConfig['decorator'])) {
            $machineDefinition->addSetup('decorateTransitions', [$machineConfig['decorator']]);
        }
        return $machineDefinition;
    }

    /**
     * @param EnumColumn|string $enumClassName
     * @return EnumColumn[][]|EnumColumn[]
     */
    public static function parseMask(string $mask, string $enumClassName): array
    {
        [$sources, $target] = explode('->', $mask);
        return [
            array_map(
                fn(string $state): ?EnumColumn => $enumClassName::tryFrom($state),
                explode('|', $sources)
            ),
            $enumClassName::tryFrom($target),
        ];
    }
}
