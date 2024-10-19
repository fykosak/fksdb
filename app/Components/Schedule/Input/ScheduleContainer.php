<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Input;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use Nette\Application\BadRequestException;
use Nette\DI\Container as DIContainer;

/**
 * @phpstan-import-type TMeta from SectionContainer
 */
class ScheduleContainer extends ContainerWithOptions
{
    /**
     * @phpstan-param TMeta[] $definition
     * @throws BadRequestException
     */
    public function __construct(
        DIContainer $container,
        array $definition,
        private readonly EventModel $event
    ) {
        parent::__construct($container);
        foreach ($definition as $sectionName => $datum) {
            $scheduleSubContainer = new SectionContainer($this->container, $this->event, $datum);
            $this->addComponent($scheduleSubContainer, $sectionName);
        }
    }

    public function save(PersonModel $person): void
    {
        /** @var SectionContainer $scheduleContainer */
        foreach ($this->getComponents(true, SectionContainer::class) as $scheduleContainer) {
            $scheduleContainer->save($person);
        }
    }

    public function setDefaultPerson(PersonModel $person): void
    {
        /** @var SectionContainer $scheduleContainer */
        foreach ($this->getComponents(true, SectionContainer::class) as $scheduleContainer) {
            $scheduleContainer->setPerson($person);
        }
    }
}
