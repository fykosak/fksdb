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
 * @phpstan-type TMeta array{
 * types:string[],
 * label:string,
 * description?:string,
 * required?:bool,
 * collapseSelf?:bool,
 * collapseChild?:bool,
 * groupBy?:self::GROUP_*}
 */
class ScheduleContainer extends ContainerWithOptions
{
    public const GROUP_DATE = 'date';
    public const GROUP_NONE = 'none';
    public const GROUP_ACCOMMODATION = 'accommodation';

    private EventModel $event;
    /** @var string[] */
    private array $types;
    private bool $required;
    /** @phpstan-var GettextTranslator<'cs'|'en'> $translator */
    private GettextTranslator $translator;
    private string $label;
    private ?string $description;
    private bool $collapseChild;
    /** @phpstan-var self::GROUP_* */
    private string $groupBy;

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
        $this->required = (bool)($meta['required'] ?? false);
        $this->label = $meta['label'];
        $this->description = $meta['description'] ?? null;
        $this->collapseChild = $meta['collapseChild'] ?? false;
        $this->collapse = $meta['collapseSelf'] ?? false;
        $this->groupBy = $meta['groupBy'] ?? self::GROUP_NONE;
        $this->createContainers();
    }

    /**
     * @phpstan-param GettextTranslator<'cs'|'en'> $translator
     */
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
        /** @var ScheduleGroupModel[] $containerGroups */
        $containerGroups = [];
        /** @var ScheduleGroupModel $scheduleGroup */
        foreach ($groups as $scheduleGroup) {
            $key = $this->getGroupKey($scheduleGroup);
            $containerGroups[$key] = $containerGroups[$key] ?? [];
            $containerGroups[$key][] = $scheduleGroup;
        }
        foreach ($containerGroups as $key => $day) {
            $formContainer = new ContainerWithOptions($this->container);
            $formContainer->collapse = $this->collapseChild;
            $formContainer->setOption('label', $this->getGroupLabel(reset($day)));
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

    public function getGroupLabel(ScheduleGroupModel $group): ?string
    {
        switch ($this->groupBy) {
            default:
            case self::GROUP_NONE:
                return null;
            case self::GROUP_DATE:
                return $group->start->format(_('__date'));
        }
    }

    public function getGroupKey(ScheduleGroupModel $group): string
    {
        switch ($this->groupBy) {
            default:
            case self::GROUP_NONE:
                return 'none';
            case self::GROUP_DATE:
                return $group->start->format('Y_m_d');
        }
    }

    public function setModel(?PersonModel $person): void
    {
        $data = [];
        if ($person) {
            $query = $person->getSchedule()
                ->where('schedule_item.schedule_group.schedule_group_type', $this->types)
                ->where('schedule_item.schedule_group.event_id', $this->event->event_id);
            /** @var PersonScheduleModel $personSchedule */
            foreach ($query as $personSchedule) {
                $group = $personSchedule->schedule_item->schedule_group;
                $key = $this->getGroupKey($group);
                /**
                 * @var ScheduleGroupField $select
                 * @phpstan-ignore-next-line
                 */
                $select = $this->getComponent($key)->getComponent((string)$group->schedule_group_id);
                $select->setModel($personSchedule);
            }
        }

        $this->setDefaults($data);
    }
}
