<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Input;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\Localization\GettextTranslator;
use Nette\Application\BadRequestException;
use Nette\DI\Container;

class ScheduleContainer extends ContainerWithOptions
{
    private EventModel $event;
    /** @var string[] */
    private array $types;
    private bool $required;
    private GettextTranslator $translator;

    /**
     * @throws BadRequestException
     */
    public function __construct(
        Container $container,
        EventModel $event,
        array $meta
    ) {
        parent::__construct($container);
        $this->event = $event;
        $this->types = $meta['types'];
        $this->required = (bool)($meta['meta']['required'] ?? false);

        $groups = $this->event->getScheduleGroups()
            ->where('schedule_group_type', $this->types)
            ->order('start, schedule_group_id');

        if ($groups->count('*') > 1) {
            $this->setOption('label', ScheduleGroupType::from($this->types[0])->label());
        }
        /** @var ScheduleGroupModel[] $days */
        $days = [];
        /** @var ScheduleGroupModel $group */
        foreach ($groups as $group) {
            $key = $group->start->format('d_m');
            $days[$key] = $days[$key] ?? [];
            $days[$key][] = $group;
        }
        foreach ($days as $key => $day) {
            $formContainer = new ContainerWithOptions($this->container);
            $formContainer->setOption('collapse', $meta['meta']['collapse'] ?? false);
            $formContainer->setOption('label', reset($day)->start->format('c'));
            $this->addComponent($formContainer, $key);
            foreach ($day as $group) {
                $field = new ScheduleGroupField($group, Language::from($this->translator->lang));
                if ($this->required) {
                    $field->setRequired(_('Field %label is required.'));
                }
                $formContainer->addComponent(
                    $field,
                    (string)$group->schedule_group_id
                );
            }
        }
    }

    public function inject(GettextTranslator $translator): void
    {
        $this->translator = $translator;
    }
}
