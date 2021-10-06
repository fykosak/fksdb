<?php

namespace FKSDB\Components\Forms\Controls\Schedule;

use FKSDB\Components\React\ReactComponentTrait;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleGroup;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\Models\ORM\Services\Schedule\ServiceScheduleItem;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\TextInput;

class ScheduleField extends TextInput {

    use ReactComponentTrait;

    private ModelEvent $event;
    private string $type;
    private ServiceScheduleItem $serviceScheduleItem;

    /**
     * @throws BadRequestException
     * @throws NotImplementedException
     */
    public function __construct(ModelEvent $event, string $type, ServiceScheduleItem $serviceScheduleItem, ?string $label) {
        parent::__construct($label ?? $this->getDefaultLabel($type));
        $this->event = $event;
        $this->type = $type;
        $this->serviceScheduleItem = $serviceScheduleItem;
        $this->registerReact('event.schedule.' . $type);
        $this->appendProperty();
    }

    /**
     * @throws NotImplementedException
     */
    private function getDefaultLabel(string $type): string {
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

    protected function getData(): array {
        $groups = $this->event->getScheduleGroups()->where('schedule_group_type', $this->type);
        $groupList = [];
        foreach ($groups as $row) {
            $group = ModelScheduleGroup::createFromActiveRow($row);
            $groupList[] = $this->serializeGroup($group);
        }
        $options = $this->getRenderOptions();
        return ['groups' => $groupList, 'options' => $options];
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
        foreach ($group->getItems() as $row) {
            $item = ModelScheduleItem::createFromActiveRow($row);
            $itemList[] = $item->__toArray();
        }

        $groupArray['items'] = $itemList;
        return $groupArray;
    }
}
