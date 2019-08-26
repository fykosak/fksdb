<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\RadioList;
use Tracy\Debugger;

/**
 * Class PersonScheduleFactory
 * @package FKSDB\Components\Forms\Factories
 */
class PersonScheduleFactory {
    /**
     * @param $fieldName
     * @param ModelEvent $event
     * @return BaseControl
     */
    public function createField($fieldName, ModelEvent $event) {
        $items = $event->getScheduleGroups()->where('schedule_group_type', $fieldName);
        $itemList = [];
        foreach ($items as $row) {
            $item = ModelScheduleItem::createFromActiveRow($row);
            Debugger::barDump($item);
            $itemList[$item->schedule_item_id] = $item->getLabel();
            Debugger::barDump($item);
        }
        $control = new RadioList();
        $control->setItems($itemList);
        Debugger::barDump($fieldName);
        Debugger::barDump($event);
        return new ModelContainer();
    }
}
