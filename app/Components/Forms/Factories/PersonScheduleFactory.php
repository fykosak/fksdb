<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Controls\Schedule\ScheduleField;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\Schedule\ScheduleItemService;
use Nette\Application\BadRequestException;

class PersonScheduleFactory
{
    private ScheduleItemService $scheduleItemService;

    public function __construct(ScheduleItemService $scheduleItemService)
    {
        $this->scheduleItemService = $scheduleItemService;
    }

    /**
     * @throws BadRequestException
     * @throws NotImplementedException
     */
    public function createField(string $fieldName, EventModel $event, ?string $label): ScheduleField
    {
        return new ScheduleField($event, $fieldName, $this->scheduleItemService, $label);
    }
}
