<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Input;

use Nette\Utils\Html;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use FKSDB\Models\Schedule\PaymentDeadlineStrategy\PaymentDeadlineStrategy;
use Fykosak\Utils\Localization\GettextTranslator;
use Fykosak\Utils\Localization\LangMap;
use Nette\Application\BadRequestException;
use Nette\DI\Container;

/**
 * @phpstan-type TMeta array{
 * filter:array{types?:ScheduleGroupType[]},
 * label:LangMap<'cs'|'en',string>,
 * description?:LangMap<'cs'|'en',string>,
 * required?:bool,
 * collapseSelf?:bool,
 * collapseChild?:bool,
 * groupBy?:SectionGroupOptions,
 * paymentDeadline?:PaymentDeadlineStrategy,
 * }
 */
class SectionContainer extends ContainerWithOptions
{
    /**
     * @phpstan-var GettextTranslator<'cs'|'en'>
     */
    private GettextTranslator $translator;
    /**
     * @phpstan-param TMeta $meta
     * @throws BadRequestException
     */
    public function __construct(
        Container $container,
        private readonly EventModel $event,
        array $meta
    ) {
        parent::__construct($container);
        $this->collapse = $meta['collapseSelf'] ?? false;
        $this->createContainers($meta);
    }

    /**
     * @phpstan-param GettextTranslator<'cs'|'en'> $translator
     */
    public function injectTranslator(GettextTranslator $translator): void
    {
        $this->translator = $translator;
    }

    public function save(PersonModel $person): void
    {
        /** @var ScheduleSelectBox $scheduleSelectBox */
        foreach ($this->getComponents(true, ScheduleSelectBox::class) as $scheduleSelectBox) {
            $scheduleSelectBox->save($person);
        }
    }

    /**
     * @throws BadRequestException
     * @phpstan-param TMeta $meta
     */
    private function createContainers(array $meta): void
    {
        $groups = $this->event->getScheduleGroups()->order('start, schedule_group_id');
        foreach ($meta['filter'] as $key => $filter) {
            switch ($key) {
                case 'types':
                    $groups->where(
                        'schedule_group_type',
                        array_map(fn(ScheduleGroupType $case) => $case->value, $filter)
                    );
                    break;
            }
        }
        $groupBy = $meta['groupBy'] ?? SectionGroupOptions::GroupNone;
        $this->setOption('label', $meta['label']->get($this->translator->lang));
        if (isset($meta['description'])) {
            $this->setOption('description', $meta['description']->get($this->translator->lang));
        }
        /** @var ScheduleGroupModel[] $containerGroups */
        $containerGroups = [];
        /** @var ScheduleGroupModel $scheduleGroup */
        foreach ($groups as $scheduleGroup) {
            $key = $groupBy->getGroupKey($scheduleGroup);
            $containerGroups[$key] = $containerGroups[$key] ?? [];
            $containerGroups[$key][] = $scheduleGroup;
        }
        foreach ($containerGroups as $key => $day) {
            $formContainer = new ContainerWithOptions($this->container);
            $formContainer->collapse = $meta['collapseChild'] ?? false;
            $formContainer->setOption('label', $groupBy->getGroupLabel(reset($day)));
            $this->addComponent($formContainer, $key);
            foreach ($day as $group) {
                $field = new ScheduleSelectBox($group, $this->container, $meta);
                if ($meta['required'] ?? false) {
                    $field->setRequired(_('Field %label is required.'));
                }
                $formContainer->addComponent(
                    $field,
                    (string)$group->schedule_group_id
                );
            }
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
