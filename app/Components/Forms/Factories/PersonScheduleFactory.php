<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Controls\Schedule\ScheduleField;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Schedule\ServiceScheduleItem;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\JsonException;

/**
 * Class PersonScheduleFactory
 * @package FKSDB\Components\Forms\Factories
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
        $this->serviceScheduleItem=$serviceScheduleItem;
    }

    /**
     * @param $fieldName
     * @param ModelEvent $event
     * @return BaseControl
     * @throws JsonException
     * @throws \FKSDB\NotImplementedException
     */
    public function createField($fieldName, ModelEvent $event) {
        return new ScheduleField($event, $fieldName,$this->serviceScheduleItem);
    }
}
