<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Controls\Schedule\ScheduleField;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\Schedule\ServiceScheduleItem;
use Nette\Application\BadRequestException;

class PersonScheduleFactory {

    private ServiceScheduleItem $serviceScheduleItem;

    public function __construct(ServiceScheduleItem $serviceScheduleItem) {
        $this->serviceScheduleItem = $serviceScheduleItem;
    }

    /**
     * @throws BadRequestException
     * @throws NotImplementedException
     */
    public function createField(string $fieldName, ModelEvent $event, ?string $label): ScheduleField {
        return new ScheduleField($event, $fieldName, $this->serviceScheduleItem, $label);
    }
}
