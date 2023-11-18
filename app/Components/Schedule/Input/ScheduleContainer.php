<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Input;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\Localization\GettextTranslator;
use Nette\Application\BadRequestException;
use Nette\DI\Container;

/**
 * @phpstan-type TMeta array{types:string[],meta:array{label:string,description?:string,required?:bool,collapse?:bool}}
 */
class ScheduleContainer extends ContainerWithOptions
{
    private EventModel $event;
    /** @var string[] */
    private array $types;
    private bool $required;
    private GettextTranslator $translator;
    private string $label;
    private ?string $description;
    private bool $collapse;

    /**
     * @phpstan-param TMeta $meta
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
        $this->label = $meta['meta']['label'];
        $this->description = $meta['meta']['description'] ?? null;
        $this->collapse = $meta['meta']['collapse'] ?? false;
        $this->createContainers();
    }

    public function inject(GettextTranslator $translator): void
    {
        $this->translator = $translator;
    }

    /**
     * @throws BadRequestException
     */
    public function createContainers(): void
    {
        $groups = $this->event->getScheduleGroups()
            ->where('schedule_group_type', $this->types)
            ->order('start, schedule_group_id');

        $this->setOption('label', $this->label);
        $this->setOption('description', $this->description);
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
            $formContainer->setOption('collapse', $this->collapse);
            $formContainer->setOption('label', reset($day)->start->format(_('__date'))); //TODO Date
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

    public function setModel(PersonModel $person): void
    {
        $query = $person->getSchedule()->where('schedule_item.schedule_group.schedule_group_type', $this->types);
        $data = [];
        /** @var PersonScheduleModel $personSchedule */
        foreach ($query as $personSchedule) {
            $group = $personSchedule->schedule_item->schedule_group;
            $key = $group->start->format('d_m');
            $data[$key] = $data[$key] ?? [];
            $data[$key][$group->schedule_group_id] = $personSchedule->schedule_item_id;
        }
        $this->setValues($data);
    }
}
