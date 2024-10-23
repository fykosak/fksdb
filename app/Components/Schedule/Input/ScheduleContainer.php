<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Input;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use FKSDB\Models\Schedule\PaymentDeadlineStrategy\PaymentDeadlineStrategy;
use Fykosak\Utils\Localization\GettextTranslator;
use Nette\Application\BadRequestException;
use Nette\DI\Container;

/**
 * @phpstan-type TMeta array{
 * types:ScheduleGroupType[],
 * label:string,
 * description?:string,
 * required?:bool,
 * collapseSelf?:bool,
 * collapseChild?:bool,
 * groupBy?:self::Group*,
 * paymentDeadline?:PaymentDeadlineStrategy,
 * }
 */
class ScheduleContainer extends ContainerWithOptions
{
    public const GroupBegin = 'date';// phpcs:ignore
    public const GroupNone = 'none';// phpcs:ignore

    private EventModel $event;
    /** @var ScheduleGroupType[] */
    private array $types;
    private bool $required;
    private GettextTranslator $translator;
    private string $label;
    private ?string $description;
    private bool $collapseChild;
    /** @phpstan-var self::Group* */
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
        $this->groupBy = $meta['groupBy'] ?? self::GroupNone;
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
            ->where('schedule_group_type', array_map(fn(ScheduleGroupType $case) => $case->value, $this->types))
            ->order('start, schedule_group_id');

        $this->setOption('label', $this->label);
        $this->setOption('description', $this->description);
        /** @var ScheduleGroupModel[][] $containerGroups */
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
            $formContainer->setOption('label', $this->getGroupLabel(reset($day)));//@phpstan-ignore-line
            $this->addComponent($formContainer, $key);
            foreach ($day as $group) {
                $field = new ScheduleSelectBox($group, $this->translator);
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
            case self::GroupNone:
                return null;
            case self::GroupBegin:
                return $group->start->format(_('__date'));
        }
    }

    public function getGroupKey(ScheduleGroupModel $group): string
    {
        switch ($this->groupBy) {
            default:
            case self::GroupNone:
                return 'none';
            case self::GroupBegin:
                return $group->start->format('Y_m_d');
        }
    }

    public function setPerson(?PersonModel $person): void
    {
        $components = $this->getComponents(true, ScheduleSelectBox::class);
        /** @var ScheduleSelectBox $select $select */
        foreach ($components as $select) {
            $select->setPerson($person);
        }
        $this->setDefaults([]);
    }
}
