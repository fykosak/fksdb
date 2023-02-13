<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\Schedule;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use Fykosak\Utils\Localization\GettextTranslator;
use Nette\Application\BadRequestException;
use Nette\DI\Container;

class ScheduleContainer extends ContainerWithOptions
{
    private EventModel $event;
    private ScheduleGroupType $type;
    private bool $required;
    private GettextTranslator $translator;

    /**
     * @throws BadRequestException
     */
    public function __construct(
        Container $container,
        EventModel $event,
        ScheduleGroupType $type,
        bool $required = false
    ) {
        parent::__construct($container);
        $this->event = $event;
        $this->type = $type;
        $this->required = $required;

        $groups = $this->event->getScheduleGroups()
                              ->where('schedule_group_type', $this->type->value)
                              ->order('start, schedule_group_id');

        if ($groups->count('*') > 1) {
            $this->setOption('label', $type->label());
        }
        /** @var ScheduleGroupModel $group */
        foreach ($groups as $group) {
            $field = new ScheduleGroupField($group, (string)$this->translator->lang);
            if ($this->required) {
                $field->setRequired(_('Field %label is required.'));
            }
            $this->addComponent(
                $field,
                (string)$group->schedule_group_id
            );
        }
    }

    public function inject(GettextTranslator $translator): void
    {
        $this->translator = $translator;
    }
}
