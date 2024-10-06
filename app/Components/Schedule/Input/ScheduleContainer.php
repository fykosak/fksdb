<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Input;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Utils\FormUtils;
use Nette\DI\Container as DIContainer;

class ScheduleContainer extends ContainerWithOptions
{
    private array $definition;
    private EventModel $event;

    public function __construct(DIContainer $container, array $definition, EventModel $event)
    {
        parent::__construct($container);
        $this->event = $event;
        $this->definition = $definition;
        foreach ($definition as $sectionName => $datum) {
            $scheduleSubContainer = new SectionContainer($this->container, $this->event, $datum);
            $this->addComponent($scheduleSubContainer, $sectionName);
        }
    }

    public function save(PersonModel $person): void
    {
        $handler = new ScheduleHandler($this->container, $this->event);
        $values = FormUtils::emptyStrToNull2($this->getValues('array'));
        $handler->handle(
        /** @phpstan-ignore-next-line */
            $values,
            $this->definition,
            $person
        );
    }

    public function setDefaultPerson(PersonModel $person): void
    {
        foreach ($this->getComponents(true, SectionContainer::class) as $scheduleContainer) {
            $scheduleContainer->setPerson($person);
        }
    }
}
