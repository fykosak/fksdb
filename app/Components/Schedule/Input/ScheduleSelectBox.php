<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Input;

use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleState;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponentTrait;
use Fykosak\Utils\Localization\GettextTranslator;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\Forms\Controls\SelectBox;

/**
 * @phpstan-import-type TMeta from SectionContainer
 */
class ScheduleSelectBox extends SelectBox
{
    use FrontEndComponentTrait;

    private PersonScheduleService $service;
    /** @phpstan-var GettextTranslator<'cs'|'en'>  */
    private GettextTranslator $translator;

    /**
     * @throws BadRequestException
     * @phpstan-param TMeta $definition
     */
    public function __construct(
        private readonly ScheduleGroupModel $group,
        Container $container,
        private readonly array $definition
    ) {
        $container->callInjects($this);
        if ($group->registration_end) {
            parent::__construct(
                sprintf(
                    _('%s -- end of registration: %s'),
                    $group->getName()->get($this->translator->lang),
                    $group->registration_end->format(_('__date_time'))
                )
            );
        } else {
            parent::__construct($group->getName()->get($this->translator->lang));
        }

        $this->registerFrontend('schedule.group-container');
        $this->appendProperty();
        $items = [];
        $disabled = [];
        /** @var ScheduleItemModel $item */
        foreach ($this->group->getItems() as $item) {
            $items[$item->getPrimary()] = sprintf(
                _('%s - %s'),
                $item->name->get($this->translator->lang),
                $item->description->get($this->translator->lang)
            );
            if (!$item->available) {
                $disabled[] = $item->getPrimary();
            }
        }
        $this->setItems($items)->setPrompt(_('Not selected'))->setDisabled($disabled);
    }

    /**
     * @param GettextTranslator<'cs'|'en'> $translator
     */
    public function inject(PersonScheduleService $service, GettextTranslator $translator): void
    {
        $this->service = $service;
        $this->translator = $translator;
    }

    public function save(PersonModel $person): void
    {
        $value = $this->getValue();
        if ($this->group->schedule_group_type === ScheduleGroupType::Info) {
            throw new ScheduleException($this->group, _('Info block cannot be selected'));
        }
        $personSchedule = $person->getScheduleByGroup($this->group);
        // already booked
        if ($personSchedule && $personSchedule->schedule_item_id === $value) {
            return;
        }
        if (!$value) {
            // Delete
            if ($personSchedule) {
                $this->handleDelete($this->group, $personSchedule);
                return;
            }
            // do nothing
            return;
        }

        /** @var ScheduleItemModel|null $item */
        $item = $this->group->getItems()->where('schedule_item_id', $value)->fetch();
        if (!$item) {
            throw new ScheduleException($this->group, sprintf(_('Item with Id %s does not exists'), $value));
        }
        // is available via GUI?
        if (!$item->available) {
            throw new ScheduleException(
                $this->group,
                sprintf(_('Item with Id %s is not available'), $item->name->get($this->translator->lang))
            );
        }
        // is modifiable?
        if (!$this->group->isModifiable()) {
            throw new ScheduleException(
                $this->group,
                sprintf(
                    _('Schedule "%s" is not allowed at this time'),
                    $this->group->getName()->get($this->translator->lang)
                )
            );
        }
        // cannot be changed when payment exists
        if ($personSchedule && $personSchedule->getPayment()) {
            throw new ExistingPaymentException($personSchedule);
        }

        // check group capacity
        if (!$this->group->hasFreeCapacity()) {
            throw new FullCapacityException($item, $person, $this->translator);
        }
        // check item capacity
        if (isset($item->capacity) && ($item->capacity <= $item->getUsedCapacity(true))) {
            throw new FullCapacityException($item, $person, $this->translator);
        }
        if ($item->require_id_number && !isset($person->getInfo()->id_number)) {
            throw new RequiredIdNumberException($person, $item);
        }
        $data = [
            'person_id' => $person->person_id,
            'schedule_item_id' => $value,
            'state' => PersonScheduleState::Applied->value,
        ];
        if (isset($this->definition['paymentDeadline'])) {
            $data['payment_deadline'] = $this->definition['paymentDeadline']->invoke($item);
        }
        $this->service->storeModel($data, $personSchedule);
    }

    private function handleDelete(ScheduleGroupModel $group, PersonScheduleModel $personSchedule): void
    {
        if (!$group->isModifiable()) {
            throw new ScheduleException($group, _('Modification of this item is not allowed at this time'));
        }
        if ($personSchedule->getPayment()) {
            throw new ExistingPaymentException($personSchedule);
        }
        $this->service->storeModel(['state' => PersonScheduleState::Canceled->value], $personSchedule);
    }

    public function setPerson(?PersonModel $person): void
    {
        if ($person) {
            $personSchedule = $person->getScheduleByGroup($this->group);
            if ($personSchedule) {
                /** @phpstan-ignore-next-line */
                if (is_array($this->disabled) && isset($this->disabled[$personSchedule->schedule_item_id])) {
                    unset($this->disabled[$personSchedule->schedule_item_id]);
                }
                parent::setDefaultValue($personSchedule->schedule_item_id);
            }
        }
    }

    /**
     * @throws \Exception
     * @phpstan-return array{
     *     group:array<string,mixed>,
     * }
     */
    protected function getData(): array
    {
        $group = $this->group->__toArray();
        $itemList = [];
        /** @var ScheduleItemModel $item */
        foreach ($this->group->getItems() as $item) {
            $itemList[] = [
                'scheduleGroupId' => $item->schedule_group_id,
                'price' => $item->getPrice()->__serialize(),
                'totalCapacity' => $item->capacity,
                'usedCapacity' => $item->getUsedCapacity(),
                'scheduleItemId' => $item->schedule_item_id,
                'name' => $item->name->__serialize(),
                'begin' => $item->getBegin(),
                'end' => $item->getEnd(),
                'description' => $item->description->__serialize(),
                'longDescription' => $item->long_description->__serialize(),
                'available' => (bool)$item->available,
                'requireIdNumber' => $item->require_id_number,
            ];
        }

        $group['items'] = $itemList;
        return ['group' => $group,];
    }
}
