<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions;

use FKSDB\Models\Expressions\Helpers;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Transitions\Transition\BehaviorType;
use Nette\DI\CompilerExtension;
use FKSDB\Models\Transitions\Transition\Transition;
use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\ServiceDefinition;

class TransitionsExtension extends CompilerExtension
{
    public function loadConfiguration(): void
    {
        parent::loadConfiguration();
        $config = $this->getConfig();
        foreach ($config as $machineName => $machine) {
            $enumClassName = $machine['stateEnum'];
            foreach ($machine['transitions'] as $mask => $transition) {
                [$sources, $target] = self::parseMask($mask, $enumClassName);
                foreach ($sources as $source) {
                    self::createCommonTransition(
                        $this,
                        $this->getContainerBuilder(),
                        $machineName,
                        $source,
                        $target,
                        $transition
                    );
                }
            }
        }
    }

    public function beforeCompile(): void
    {
        parent::beforeCompile();
        $config = $this->getConfig();
        foreach ($config as $machineName => $machine) {
            $this->setUpMachine($machineName, $machine);
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
            ->addSetup('setLabel', [Helpers::translate($baseConfig['label'])])
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

    private function setUpMachine(string $machineName, array $machineConfig): void
    {
        $builder = $this->getContainerBuilder();
        $machineDefinition = $builder->getDefinition($machineConfig['machine']);
        foreach ($builder->findByTag($machineName) as $name => $transition) {
            $machineDefinition->addSetup('addTransition', [$builder->getDefinition($name)]);
        }
        if (isset($machineConfig['decorator'])) {
            $machineDefinition->addSetup('decorateTransitions', [$machineConfig['decorator']]);
        }
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
