<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Controls\Schedule\ScheduleField;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Schedule\ServiceScheduleItem;
use Nette\Application\BadRequestException;
use Nette\Utils\JsonException;

/**
 * Class PersonScheduleFactory
 * *
 */
class PersonScheduleFactory {
    /**
     * @var ServiceScheduleItem
     */
    private $serviceScheduleItem;

    /**
     * PersonScheduleFactory constructor.
     * @param ServiceScheduleItem $serviceScheduleItem
     */
    public function __construct(ServiceScheduleItem $serviceScheduleItem) {
        $this->serviceScheduleItem = $serviceScheduleItem;
    }

    /**
     * @param $fieldName
     * @param ModelEvent $event
     * @return ScheduleField
     * @throws JsonException
     * @throws NotImplementedException
     * @throws BadRequestException
     */
    public function createField($fieldName, ModelEvent $event) {
        return new ScheduleField($event, $fieldName, $this->serviceScheduleItem);
    }
}
