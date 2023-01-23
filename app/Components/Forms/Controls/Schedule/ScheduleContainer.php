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
    private bool $required;

    public function __construct(
        Container $container,
        EventModel $event,
        ScheduleGroupType $type,
        string $lang,
        bool $required = false
    ) {
        parent::__construct($container);
        $this->monitor(Presenter::class, fn() => $this->configure());
        $this->event = $event;
        $this->type = $type;
        $this->lang = $lang;
        $this->required = $required;
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
            $field = new ScheduleGroupField($group, $this->lang);
            $field->setRequired($this->required);
            $this->addComponent(
                $field,
                (string)$group->schedule_group_id
            );
        }
    }
}
