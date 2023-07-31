<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions;

use FKSDB\Models\Expressions\Helpers;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Transitions\Transition\BehaviorType;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Models\Utils\FakeStringEnum;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

class TransitionsExtension extends CompilerExtension
{
    public function getConfigSchema(): Schema
    {
        return Expect::arrayOf(self::getMachineSchema(), Expect::string());
    }

    public static function getMachineSchema(): Structure
    {
        return Expect::structure([
            'machine' => Expect::string(),
            'stateEnum' => Expect::string(),
            'decorator' => Expect::type(\Nette\DI\Definitions\Statement::class)->nullable(),
            'transitions' => Expect::arrayOf(
                Expect::structure([
                    'condition' => Helpers::createBoolExpressionSchemaType(true)->default(true),
                    'label' => Helpers::createExpressionSchemaType(),
                    'validation' => Helpers::createBoolExpressionSchemaType(true)->default(true),
                    'afterExecute' => Expect::listOf(Helpers::createExpressionSchemaType()),
                    'beforeExecute' => Expect::listOf(Helpers::createExpressionSchemaType()),
                    'behaviorType' => Expect::anyOf('success', 'warning', 'danger', 'primary', 'secondary')
                        ->default('secondary'),
                ])->castTo('array'),
                Expect::string()
            ),
        ])->castTo('array');
    }

    public function loadConfiguration(): void
    {
        parent::loadConfiguration();
        $config = $this->getConfig();
        foreach ($config as $machineName => $machine) {
            self::createMachine($this, $machineName, $machine);
        }
    }
    public static function createMachine(CompilerExtension $extension, string $name, array $config): ServiceDefinition
    {
        $factory = $extension->getContainerBuilder()
            ->addDefinition($extension->prefix($name . '.machine'))
            ->setFactory($config['machine']);
        foreach ($config['transitions'] as $mask => $transitionConfig) {
            [$sources, $target] = self::parseMask($mask, $config['stateEnum']);
            foreach ($sources as $source) {
                $transition = $extension->getContainerBuilder()->addDefinition(
                    $extension->prefix(
                        $name . '.' .
                        ($source->value) . '.' .
                        ($target->value)
                    )
                )
                    ->addTag($name)
                    ->setType(Transition::class)
                    ->addSetup('setValidation', [$transitionConfig['validation']])
                    ->addSetup('setCondition', [$transitionConfig['condition']])
                    ->addSetup('setSourceStateEnum', [$source])
                    ->addSetup('setTargetStateEnum', [$target])
                    ->addSetup('setLabel', [Helpers::resolveMixedExpression($transitionConfig['label'])])
                    ->addSetup(
                        'setBehaviorType',
                        [
                            BehaviorType::tryFrom($transitionConfig['behaviorType']),
                        ]
                    );
                foreach ($transitionConfig['afterExecute'] as $callback) {
                    $transition->addSetup('addAfterExecute', [$callback]);
                }
                foreach ($transitionConfig['beforeExecute'] as $callback) {
                    $transition->addSetup('addBeforeExecute', [$callback]);
                }
                $factory->addSetup('addTransition', [$transition]);
            }
        }
        if (isset($config['decorator'])) {
            $factory->addSetup('decorateTransitions', [$config['decorator']]);
        }
        return $factory;
    }

    /**
     * @param class-string<EnumColumn&FakeStringEnum> $enumClassName
     * @return array{(EnumColumn&FakeStringEnum)[],(EnumColumn&FakeStringEnum)|null}
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
