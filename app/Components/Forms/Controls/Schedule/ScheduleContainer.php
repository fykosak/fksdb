<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\Schedule;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use Fykosak\Utils\Localization\GettextTranslator;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;

class ScheduleContainer extends ContainerWithOptions
{
    private EventModel $event;
    private ScheduleGroupType $type;
    private bool $required;
    private GettextTranslator $translator;

    public function __construct(
        Container $container,
        EventModel $event,
        ScheduleGroupType $type,
        bool $required = false
    ) {
        parent::__construct($container);
        $this->monitor(Presenter::class, fn() => $this->configure());
        $this->event = $event;
        $this->type = $type;
        $this->required = $required;
    }

    public function inject(GettextTranslator $translator): void
    {
        $this->translator = $translator;
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
            $field = new ScheduleGroupField($group, $this->translator->lang);
            $field->setRequired($this->required);
            $this->addComponent(
                $field,
                (string)$group->schedule_group_id
            );
        }
    }
}
