<?php

namespace FKSDB\Payment\Transition;

use FKSDB\Transitions\Transition;
use Nette\DI\CompilerExtension;
use Nette\DI\Statement;

/**
 * Class PaymentTransitionsExtension
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TransitionsExtension extends CompilerExtension {

    public function loadConfiguration(): void {
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

    public function beforeCompile(): void {
        parent::beforeCompile();
        $config = $this->getConfig();
        foreach ($config as $machineName => $machine) {
            $this->setUpMachine($machineName, $machine);
        }
    }

    private function setUpMachine(string $machineName, array $machineConfig): void {
        $builder = $this->getContainerBuilder();
        $machineDefinition = $builder->getDefinition($machineConfig['machine']);
        foreach ($builder->findByTag($machineName) as $name => $transition) {
            $machineDefinition->addSetup('addTransition', [$builder->getDefinition($name)]);
        }
        if (isset($machine['transitionsDecorator'])) {
            $machineDefinition->addSetup('decorateTransitions', [$machine['transitionsDecorator']]);
        }
    }

    private function createTransition(string $machineName, string $source, string $target, array $transitionConfig): void {
        $builder = $this->getContainerBuilder();
        $factory = $builder->addDefinition($this->prefix($machineName . '.' . $source . '.' . $target))
            ->addTag($machineName)
            ->setFactory(Transition::class, [$source, $target, self::translate($transitionConfig['label'])]);
        if (isset($transitionConfig['behaviorType'])) {
            $factory->addSetup('setType', [$transitionConfig['behaviorType']]);
        }
        if (isset($transitionConfig['beforeExecute'])) {
            foreach ($transitionConfig['beforeExecute'] as $callback) {
                $factory->addSetup('addBeforeExecute', [$callback]);
            }
        }
    }

    /**
     * @param $statement
     * @return Statement|string
     */
    private function translate($statement) {
        if ($statement instanceof Statement && $statement->entity === '_') {
            return _(...$statement->arguments);
        }
        return $statement;
    }

    public static function parseMask(string $mask): array {
        [$source, $target] = explode('->', $mask);
        return [explode('|', $source), $target];
    }
}
