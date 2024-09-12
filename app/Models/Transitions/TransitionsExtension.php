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

/**
 * @phpstan-type Item array{
 *      machine:string,
 *      stateEnum:class-string<EnumColumn&FakeStringEnum>,
 *      decorator:\Nette\DI\Definitions\Statement|null,
 *      transitions:array<string,TransitionType>
 * }
 * @phpstan-type TransitionType array{
 *      condition:\Nette\DI\Definitions\Statement|bool|null,
 *      label:\Nette\DI\Definitions\Statement|string|null,
 *      icon: string,
 *      successLabel:string,
 *      validation:\Nette\DI\Definitions\Statement|bool|null,
 *      afterExecute:array<\Nette\DI\Definitions\Statement|string|null>,
 *      beforeExecute:array<\Nette\DI\Definitions\Statement|string|null>,
 *      onFail:array<\Nette\DI\Definitions\Statement|string|null>,
 *      behaviorType:'success'|'warning'|'danger'|'primary'|'secondary'
 *  }
 */
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
                    'icon' => Expect::string('')->required(false),
                    'successLabel' => Helpers::createExpressionSchemaType(),
                    'validation' => Helpers::createBoolExpressionSchemaType(true)->default(true),
                    'afterExecute' => Expect::listOf(Helpers::createExpressionSchemaType()),
                    'beforeExecute' => Expect::listOf(Helpers::createExpressionSchemaType()),
                    'behaviorType' => Expect::anyOf(...array_map(fn($case) => $case->value, BehaviorType::cases()))
                        ->default(BehaviorType::DEFAULT),
                    'onFail' => Expect::listOf(Helpers::createExpressionSchemaType()),
                ])->castTo('array'),
                Expect::string()
            ),
        ])->castTo('array');
    }

    public function loadConfiguration(): void
    {
        parent::loadConfiguration();
        /** @phpstan-var array<string,Item> $config */
        $config = $this->getConfig();
        foreach ($config as $machineName => $machine) {
            self::createMachine($this, $machineName, $machine);
        }
    }

    /**
     * @phpstan-param Item $config
     */
    public static function createMachine(CompilerExtension $extension, string $name, array $config): ServiceDefinition
    {
        $factory = $extension->getContainerBuilder()
            ->addDefinition($extension->prefix($name . '.machine'))
            ->setFactory($config['machine']);
        foreach ($config['transitions'] as $mask => $transitionConfig) {
            [$sources, $target] = self::parseMask($mask, $config['stateEnum']);
            foreach ($sources as $source) {
                $transition = $extension->getContainerBuilder()->addDefinition(
                    $extension->prefix($name . '.' . $source->value . '.' . $target->value)
                )
                    ->addTag($name)
                    ->setType(Transition::class)
                    ->addSetup('$service->validation=?', [$transitionConfig['validation']])
                    ->addSetup('$service->condition= is_bool(?) \? fn() => ? : ?;', [
                        $transitionConfig['condition'],
                        $transitionConfig['condition'],
                        $transitionConfig['condition'],
                    ])
                    ->addSetup('$service->source=?', [$source])
                    ->addSetup('$service->target=?', [$target])
                    ->addSetup(
                        '$service->label = new \Fykosak\Utils\UI\Title(null,?,?)',
                        [
                            Helpers::resolveMixedExpression($transitionConfig['label']),
                            $transitionConfig['icon'],
                        ]
                    )->addSetup('setSuccessLabel', [$transitionConfig['successLabel']])
                    ->addSetup(
                        '$service->behaviorType=?',
                        [
                            BehaviorType::from($transitionConfig['behaviorType']),
                        ]
                    );
                foreach ($transitionConfig['afterExecute'] as $callback) {
                    $transition->addSetup('$service->afterExecute[]=?', [$callback]);
                }
                foreach ($transitionConfig['beforeExecute'] as $callback) {
                    $transition->addSetup('$service->beforeExecute[]=?', [$callback]);
                }
                foreach ($transitionConfig['onFail'] as $callback) {
                    $transition->addSetup('$service->onFail[]=?', [$callback]);
                }
                $factory->addSetup('$service->transitions[]=?', [$transition]);
            }
        }
        if (isset($config['decorator'])) {
            $factory->addSetup('(?)->decorate($service)', [$config['decorator']]);
        }
        return $factory;
    }

    /**
     * @phpstan-template TEnum of (EnumColumn&FakeStringEnum)
     * @phpstan-param class-string<TEnum> $enumClassName
     * @phpstan-return array{TEnum[],TEnum}
     */
    public static function parseMask(string $mask, string $enumClassName): array
    {
        [$sources, $target] = explode('->', $mask);
        return [
            array_map(
                fn(string $state): EnumColumn => $enumClassName::from($state),
                explode('|', $sources)
            ),
            $enumClassName::from($target),
        ];
    }
}
