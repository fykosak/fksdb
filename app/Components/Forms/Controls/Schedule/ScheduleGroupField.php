<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\Schedule;

use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponentTrait;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\SelectBox;

class ScheduleGroupField extends SelectBox
{
    use FrontEndComponentTrait;

    private ScheduleGroupModel $group;

    /**
     * @throws BadRequestException
     */
    public function __construct(ScheduleGroupModel $group, string $lang)
    {
        $regEnd = $group->getRegistrationEnd();
        parent::__construct(
            $lang === 'cs'
                ? $group->name_cs . ' - konec registrace: ' . $regEnd
                : $group->name_en . ' - end of registration: ' . $regEnd
        );
        $this->group = $group;
        $this->registerFrontend('schedule.group-container');
        $this->appendProperty();
        $items = [];
        /** @var ScheduleItemModel $item */
        foreach ($this->group->getItems() as $item) {
            $items[$item->getPrimary()] = $lang === 'cs'
                ? ($item->name_cs . ' - ' . $item->description_cs)
                : ($item->name_en . ' - ' . $item->description_en);
        }
        $this->setItems($items)->setPrompt($lang === 'cs' ? '-- nevybráno --' : '-- not selected --');
    }

    protected function getData(): array
    {
        $group = $this->group->__toArray();
        $itemList = [];
        /** @var ScheduleItemModel $item */
        foreach ($this->group->getItems() as $item) {
            $itemList[] = $item->__toArray();
        }

        $group['items'] = $itemList;
        return [
            'group' => $group,
            'options' => $this->group->schedule_group_type->getRenderOptions(),
        ];
    }
}
