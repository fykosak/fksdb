<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions;

use FKSDB\Models\Expressions\Helpers;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Transitions\Machine\AbstractMachine;
use Nette\DI\CompilerExtension;
use FKSDB\Models\Transitions\Transition\Transition;

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
                    $this->createTransition(
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

    private function setUpMachine(string $machineName, array $machineConfig): void
    {
        $builder = $this->getContainerBuilder();
        $machineDefinition = $builder->getDefinition($machineConfig['machine']);
        foreach ($builder->findByTag($machineName) as $name => $transition) {
            $machineDefinition->addSetup('addTransition', [$builder->getDefinition($name)]);
        }
        if (isset($machineConfig['transitionsDecorator'])) {
            $machineDefinition->addSetup('decorateTransitions', [$machineConfig['transitionsDecorator']]);
        }
    }

    private function createTransition(
        string $machineName,
        ?EnumColumn $source,
        ?EnumColumn $target,
        array $transitionConfig
    ): void {
        $builder = $this->getContainerBuilder();
        $factory = $builder->addDefinition(
            $this->prefix(
                $machineName . '.' .
                ($source ? $source->value : 'INIT') . '.' .
                ($target ? $target->value : 'TERMINATED')
            )
        )
            ->addTag($machineName)
            ->setType(Transition::class)
            ->addSetup('setEvaluator', ['@events.expressionEvaluator'])
            ->addSetup('setCondition', [$transitionConfig['condition'] ?? null])
            ->addSetup('setSourceStateEnum', [$source])
            ->addSetup('setTargetStateEnum', [$target])
            ->addSetup('setLabel', [Helpers::translate($transitionConfig['label'])]);
        if (isset($transitionConfig['behaviorType'])) {
            $factory->addSetup('setBehaviorType', [$transitionConfig['behaviorType']]);
        }
        if (isset($transitionConfig['beforeExecute'])) {
            foreach ($transitionConfig['beforeExecute'] as $callback) {
                $factory->addSetup('addBeforeExecute', [$callback]);
            }
        }
        if (isset($transitionConfig['afterExecute'])) {
            foreach ($transitionConfig['afterExecute'] as $callback) {
                $factory->addSetup('addAfterExecute', [$callback]);
            }
        }
    }

    /**
     * @param EnumColumn|string $enumClassName
     * @return ?EnumColumn[][]|?EnumColumn[]
     */
    public static function parseMask(string $mask, string $enumClassName): array
    {
        [$sources, $target] = explode('->', $mask);
        /*  if ($source === AbstractMachine::STATE_ANY) {
              return [
                  array_filter($enumClassName::cases(), fn(EnumColumn $case): bool => $case->value !== $target),
                  new $enumClassName($target),
              ];
          }*/
        return [
            array_map(
                fn(string $state): ?EnumColumn => $state !== AbstractMachine::STATE_INIT ? new $enumClassName($state)
                    : null,
                explode('|', $sources)
            ),
            $target !== AbstractMachine::STATE_TERMINATED ? new $enumClassName($target) : null,
        ];
    }
}
