<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\Schedule;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use FKSDB\Models\ORM\Services\Schedule\ScheduleItemService;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponentTrait;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\TextInput;

class ScheduleField extends TextInput
{
    use FrontEndComponentTrait;

    private EventModel $event;
    private string $type;
    private ScheduleItemService $scheduleItemService;

    /**
     * @throws BadRequestException
     * @throws NotImplementedException
     */
    public function __construct(
        EventModel $event,
        string $type,
        ScheduleItemService $scheduleItemService,
        ?string $label
    ) {
        parent::__construct($label ?? $this->getDefaultLabel($type));
        $this->event = $event;
        $this->type = $type;
        $this->scheduleItemService = $scheduleItemService;
        $this->registerFrontend('event.schedule');
        $this->appendProperty();
    }

    /**
     * @throws NotImplementedException
     */
    private function getDefaultLabel(string $type): string
    {
        switch ($type) {
            case ScheduleGroupType::ACCOMMODATION:
                return _('Accommodation');
            case ScheduleGroupType::ACCOMMODATION_GENDER:
                return _('Accommodation with persons of the same gender');
            case ScheduleGroupType::VISA:
                return _('Visa');
            case ScheduleGroupType::ACCOMMODATION_TEACHER:
                return _('Teacher accommodation');
            case ScheduleGroupType::WEEKEND:
                return _('Weekend after competition');
            case ScheduleGroupType::TEACHER_PRESENT:
                return _('Program during competition');
            case ScheduleGroupType::DSEF_MORNING:
                return _('Morning');
            case ScheduleGroupType::DSEF_AFTERNOON:
                return _('Afternoon');
            case ScheduleGroupType::VACCINATION_COVID:
                return _('Covid-19 Vaccination');
            default:
                throw new NotImplementedException();
        }
    }

    protected function getData(): array
    {
        $groups = $this->event->getScheduleGroups()->where('schedule_group_type', $this->type);
        $groupList = [];
        /** @var ScheduleGroupModel $group */
        foreach ($groups as $group) {
            $groupList[] = $this->serializeGroup($group);
        }
        return ['groups' => $groupList, 'options' => $this->getRenderOptions()];
    }

    private function getRenderOptions(): array
    {
        $params = [
            'capacity' => true,
            'description' => true,
            'groupLabel' => true,
            'price' => true,
            'groupTime' => false,
        ];
        switch ($this->type) {
            case ScheduleGroupType::DSEF_AFTERNOON:
            case ScheduleGroupType::DSEF_MORNING:
                $params['price'] = false;
                $params['groupLabel'] = false;
                break;
            case ScheduleGroupType::ACCOMMODATION:
                break;
            case ScheduleGroupType::VACCINATION_COVID:
            case ScheduleGroupType::ACCOMMODATION_TEACHER:
            case ScheduleGroupType::ACCOMMODATION_GENDER:
            case ScheduleGroupType::VISA:
            case ScheduleGroupType::TEACHER_PRESENT:
                $params['capacity'] = false;
                $params['price'] = false;
                $params['groupLabel'] = false;
                break;
            case ScheduleGroupType::WEEKEND:
                $params['groupTime'] = true;
        }
        return $params;
    }

    private function serializeGroup(ScheduleGroupModel $group): array
    {
        $groupArray = $group->__toArray();
        $itemList = [];
        /** @var ScheduleItemModel $item */
        foreach ($group->getItems() as $item) {
            $itemList[] = $item->__toArray();
        }

        $groupArray['items'] = $itemList;
        return $groupArray;
    }
}
