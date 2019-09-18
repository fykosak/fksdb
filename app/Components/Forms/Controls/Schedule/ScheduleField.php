<?php

namespace FKSDB\Components\Forms\Controls\Schedule;

use Exception;
use FKSDB\Components\React\ReactField;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\Schedule\ModelScheduleGroup;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use Nette\Forms\Controls\TextInput;
use Nette\NotImplementedException;
use Nette\Utils\JsonException;

/**
 * Class ScheduleField
 * @package FKSDB\Components\Forms\Controls\Schedule
 */
class ScheduleField extends TextInput {

    use ReactField;
    /**
     * @var ModelEvent
     */
    private $event;
    /**
     * @var string
     */
    private $type;

    /**
     * ScheduleField constructor.
     * @param ModelEvent $event
     * @param string $type
     * @throws JsonException
     */
    public function __construct(ModelEvent $event, string $type) {
        parent::__construct($this->getLabelByType($type));
        $this->event = $event;
        $this->type = $type;
        $this->appendProperty();
        $this->registerMonitor();
    }

    /**
     * @param $obj
     */
    public function attached($obj) {
        parent::attached($obj);
        $this->attachedReact($obj);
    }

    /**
     * @param string $type
     * @return string
     */
    private function getLabelByType(string $type): string {
        switch ($type) {
            case 'accommodation':
                return _('Accommodation');
            default:
                throw new NotImplementedException();
        }
    }

    /**
     * @return string
     */
    public function getMode(): string {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getComponentName(): string {
        return 'schedule';
    }

    /**
     * @return string
     */
    public function getModuleName(): string {
        return 'event';
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getData(): string {
        $groups = $this->event->getScheduleGroups()->where('schedule_group_type', $this->type);
        $groupList = [];
        foreach ($groups as $row) {
            $group = ModelScheduleGroup::createFromActiveRow($row);
            $itemList = [];
            foreach ($group->getItems() as $itemRow) {
                $item = ModelScheduleItem::createFromActiveRow($itemRow);
                $itemList[] = $item->__toArray();
            }
            $groupArray = $group->__toArray();
            $groupArray['items'] = $itemList;
            $groupList[] = $groupArray;

        }
        return json_encode($groupList);
    }

    /**
     * @return array
     */
    public function getActions(): array {
        return [];
    }
}
