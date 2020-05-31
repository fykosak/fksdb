<?php

namespace FKSDB\Components\Forms\Controls\Schedule;

use Exception;
use FKSDB\Components\React\ReactField;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\Schedule\ModelScheduleGroup;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\ORM\Services\Schedule\ServiceScheduleItem;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Controls\TextInput;
use FKSDB\Exceptions\NotImplementedException;
use Nette\Utils\JsonException;

/**
 * Class ScheduleField
 * *
 */
class ScheduleField extends TextInput {
    use ReactField;

    private ModelEvent $event;

    private string $type;

    private ServiceScheduleItem $serviceScheduleItem;

    /**
     * ScheduleField constructor.
     * @param ModelEvent $event
     * @param string $type
     * @param ServiceScheduleItem $serviceScheduleItem
     * @throws JsonException
     * @throws NotImplementedException
     */
    public function __construct(ModelEvent $event, string $type, ServiceScheduleItem $serviceScheduleItem) {
        parent::__construct($this->getLabelByType($type));
        $this->event = $event;
        $this->type = $type;
        $this->serviceScheduleItem = $serviceScheduleItem;
        $this->appendProperty();
        $this->registerMonitor();
    }

    /**
     * @param IComponent $obj
     */
    public function attached($obj) {
        parent::attached($obj);
        $this->attachedReact($obj);
    }

    /**
     * @param string $type
     * @return string
     * @throws NotImplementedException
     */
    private function getLabelByType(string $type): string {
        switch ($type) {
            case ModelScheduleGroup::TYPE_ACCOMMODATION:
                return _('Accommodation');
            case ModelScheduleGroup::TYPE_ACCOMMODATION_GENDER:
                return _('Accommodation with same gender');
            case ModelScheduleGroup::TYPE_VISA:
                return _('Visa');
            case ModelScheduleGroup::TYPE_ACCOMMODATION_TEACHER:
                return _('Teacher accommodation');
            case ModelScheduleGroup::TYPE_WEEKEND:
                return _('Weekend after competition');
            case ModelScheduleGroup::TYPE_TEACHER_PRESENT:
                return _('Program during competition');
            default:
                throw new NotImplementedException();
        }
    }

    protected function getReactId(): string {
        return 'event.schedule.' . $this->type;
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
            $groupList[] = $this->serializeGroup($group);
        }
        $options = $this->getRenderOptions();
        return json_encode(['groups' => $groupList, 'options' => $options]);
    }

    private function getRenderOptions(): array {
        $params = [
            'display' => [
                'capacity' => true,
                'description' => true,
                'groupLabel' => true,
                'price' => true,
                'groupTime' => false,
            ],
        ];
        switch ($this->type) {
            case ModelScheduleGroup::TYPE_ACCOMMODATION:
                break;
            case ModelScheduleGroup::TYPE_ACCOMMODATION_TEACHER:
            case ModelScheduleGroup::TYPE_ACCOMMODATION_GENDER:
            case ModelScheduleGroup::TYPE_VISA:
            case ModelScheduleGroup::TYPE_TEACHER_PRESENT:
                $params['display']['capacity'] = false;
                $params['display']['price'] = false;
                $params['display']['groupLabel'] = false;
                break;
            case ModelScheduleGroup::TYPE_WEEKEND:
                $params['display']['groupTime'] = true;
        }
        return $params;
    }

    private function serializeGroup(ModelScheduleGroup $group): array {
        $groupArray = $group->__toArray();
        $itemList = [];
        $items = $this->serviceScheduleItem->getTable()->where('schedule_group_id', $group->schedule_group_id);
        /** @var ModelScheduleItem $item */
        foreach ($items as $item) {
            $itemList[] = $item->__toArray();
        }

        $groupArray['items'] = $itemList;
        return $groupArray;
    }
}
