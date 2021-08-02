<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions;

use FKSDB\Models\Expressions\Helpers;
use Nette\DI\CompilerExtension;
use FKSDB\Models\Transitions\Transition\Transition;

class TransitionsExtension extends CompilerExtension
{

    public function loadConfiguration(): void
    {
        parent::loadConfiguration();
        $config = $this->getConfig();
        foreach ($config as $machineName => $machine) {
            foreach ($machine['transitions'] as $mask => $transition) {
                [$sources, $target] = self::parseMask($mask);
                foreach ($sources as $source) {
                    $this->createTransition($machineName, $source, $target, $transition);
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
        string $source,
        string $target,
        array $transitionConfig
    ): void {
        $builder = $this->getContainerBuilder();
        $factory = $builder->addDefinition($this->prefix($machineName . '.' . $source . '.' . $target))
            ->addTag($machineName)
            ->setType(Transition::class)
            ->addSetup('setSourceState', [$source])
            ->addSetup('setTargetState', [$target])
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

    public static function parseMask(string $mask): array
    {
        [$source, $target] = explode('->', $mask);
        return [explode('|', $source), $target];
    }
}
