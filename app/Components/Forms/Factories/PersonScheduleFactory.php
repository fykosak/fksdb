<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Controls\Schedule\ScheduleField;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Schedule\ServiceScheduleItem;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\JsonException;

/**
 * Class PersonScheduleFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PersonScheduleFactory {

    private ServiceScheduleItem $serviceScheduleItem;

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
     * @return BaseControl
     * @throws JsonException
     * @throws NotImplementedException
     */
    public function createField($fieldName, ModelEvent $event) {
        return new ScheduleField($event, $fieldName, $this->serviceScheduleItem);
    }
}
