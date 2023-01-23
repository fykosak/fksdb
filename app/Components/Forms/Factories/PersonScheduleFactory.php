<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Controls\Schedule\ScheduleContainer;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use Nette\DI\Container;

class PersonScheduleFactory
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function createField(string $fieldName, EventModel $event, ?string $label): ScheduleContainer
    {
        return new ScheduleContainer(
            $this->container,
            $event,
            ScheduleGroupType::tryFrom($fieldName),
            'cs',
            $label
        );//TODO!!!!
    }
}
