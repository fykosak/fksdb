<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\Schedule;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;

class ScheduleContainer extends ContainerWithOptions
{
    private EventModel $event;
    private ScheduleGroupType $type;
    private string $lang;

    public function __construct(
        Container $container,
        EventModel $event,
        ScheduleGroupType $type,
        string $lang,
        ?string $label
    ) {
        parent::__construct($container);
        if ($label) {
            $this->setOption('label', $label);
        }
        $this->monitor(Presenter::class, fn() => $this->configure());
        $this->event = $event;
        $this->type = $type;
        $this->lang = $lang;
    }

    /**
     * @throws BadRequestException
     */
    private function configure(): void
    {
        // TODO order here!!!
        $groups = $this->event->getScheduleGroups()->where('schedule_group_type', $this->type->value);
        /** @var ScheduleGroupModel $group */
        foreach ($groups as $group) {
            $this->addComponent(
                new ScheduleGroupField($group, $this->lang),
                (string)$group->schedule_group_id
            );
        }
    }
}
