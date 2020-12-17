<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Controls\Schedule\ScheduleField;
use FKSDB\Model\Exceptions\NotImplementedException;
use FKSDB\Model\ORM\Models\ModelEvent;
use FKSDB\Model\ORM\Services\Schedule\ServiceScheduleItem;
use Nette\Application\BadRequestException;

/**
 * Class PersonScheduleFactory
 * *
 */
class PersonScheduleFactory {

    private ServiceScheduleItem $serviceScheduleItem;

    public function __construct(ServiceScheduleItem $serviceScheduleItem) {
        $this->serviceScheduleItem = $serviceScheduleItem;
    }

    /**
     * @param string $fieldName
     * @param ModelEvent $event
     * @return ScheduleField
     * @throws BadRequestException
     * @throws NotImplementedException
     * @throws BadRequestException
     */
    public function createField(string $fieldName, ModelEvent $event): ScheduleField {
        return new ScheduleField($event, $fieldName, $this->serviceScheduleItem);
    }
}
