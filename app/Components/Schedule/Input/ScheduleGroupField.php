<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Input;

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
            sprintf(
                _('%s - - end of registration: %s'),
                $group->name->getText($lang),
                $regEnd->format(_('__date_time'))
            )
        );
        $this->group = $group;
        $this->registerFrontend('schedule.group-container');
        $this->appendProperty();
        $items = [];
        /** @var ScheduleItemModel $item */
        foreach ($this->group->getItems() as $item) {
            $items[$item->getPrimary()] = sprintf(
                _('%s - %s'),
                $item->name->getText($lang),
                $item->description->getText($lang)
            );
        }
        $this->setItems($items)->setPrompt(_('-- not selected --'));
    }

    /**
     * @throws \Exception
     */
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
