<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\Schedule;

use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponentTrait;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\TextInput;

class ScheduleGroupField extends TextInput
{
    use FrontEndComponentTrait;

    private ScheduleGroupModel $group;

    /**
     * @throws BadRequestException
     */
    public function __construct(ScheduleGroupModel $group, string $lang)
    {
        parent::__construct($lang === 'cs' ? $group->name_cs : $group->name_en);
        $this->group = $group;
        $this->registerFrontend('schedule.group-container');
        $this->appendProperty();
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
