<?php

declare(strict_types=1);

namespace FKSDB\Models\Events;

use FKSDB\Models\Expressions\Helpers;
use FKSDB\Models\Transitions\TransitionsExtension;
use Nette\DI\CompilerExtension;
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
                            'factory' => Helpers::createExpressionSchemaType()->default(null),
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
    }
}
