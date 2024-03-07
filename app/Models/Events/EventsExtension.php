<?php

declare(strict_types=1);

namespace FKSDB\Models\Events;

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
                'machine' => TransitionsExtension::getMachineSchema(),
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
        foreach ($this->getConfig() as $definitionName => $definition) {//@phpstan-ignore-line
            $keys = $this->createAccessKeys($definition);
            $machine = TransitionsExtension::createMachine(
                $this,
                $definitionName,
                $this->getConfig()[$definitionName]['machine']//@phpstan-ignore-line
            );
            $eventDispatchFactory->addSetup(
                'addEvent',
                [$keys, $machine->getName()]
            );
        }
    }

    /**
     * @phpstan-param string[][] $definition
     * @phpstan-return string[]
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
}
