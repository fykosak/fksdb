<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Input;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleState;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use Fykosak\Utils\Localization\GettextTranslator;
use Nette\DI\Container;

/**
 * @phpstan-import-type TMeta from ScheduleContainer
 */
final class ScheduleHandler
{
    private PersonScheduleService $service;
    private GettextTranslator $translator;
    private EventModel $event;

    public function __construct(Container $container, EventModel $event)
    {
        $container->callInjects($this);
        $this->event = $event;
    }

    public function inject(PersonScheduleService $service, GettextTranslator $translator): void
    {
        $this->service = $service;
        $this->translator = $translator;
    }

    /**
     * @throws ScheduleException
     * @throws ExistingPaymentException
     * @throws FullCapacityException
     * @throws \PDOException
     * @phpstan-param array<string,array<string,array<int,int>>> $data
     * @phpstan-param array<string,TMeta> $definition
     */
    public function handle(array $data, array $definition, PersonModel $person): void
    {

        foreach ($definition as $key => $definitionDatum) {
            $datum = $data[$key] ?? [];
            foreach ($datum as $dayGroup) {
                foreach ($dayGroup as $groupId => $itemId) {
                    $this->saveGroup($person, $groupId, $itemId, $definitionDatum);
                }
            }
        }
    }

    /**
     * @phpstan-param TMeta $definition
     */
    public function saveGroup(PersonModel $person, int $groupId, ?int $value, array $definition): void
    {
        /** @var ScheduleGroupModel|null $group */
        $group = $this->event->getScheduleGroups()
            ->where('schedule_group_id', $groupId)
            ->fetch();
        if (!$group) {
            throw new ScheduleException(null, _('Schedule does not exist'));
        }
        if ($group->schedule_group_type->value === ScheduleGroupType::Info) {
            throw new ScheduleException($group, _('Info block cannot be selected'));
        }
        $personSchedule = $person->getScheduleByGroup($group);
        // already booked
        if ($personSchedule && $personSchedule->schedule_item_id === $value) {
            return;
        }
        if (!$value) {
            // Delete
            if ($personSchedule) {
                $this->handleDelete($group, $personSchedule);
                return;
            }
            // do nothing
            return;
        }

        /** @var ScheduleItemModel|null $item */
        $item = $group->getItems()->where('schedule_item_id', $value)->fetch();
        if (!$item) {
            throw new ScheduleException($group, sprintf(_('Item with Id %s does not exists'), $value));
        }
        // is available via GUI?
        if (!$item->available) {
            throw new ScheduleException(
                $group,
                sprintf(_('Item with Id %s is not available'), $this->translator->getVariant($item->name))
            );
        }
        // is modifiable?
        if (!$group->isModifiable()) {
            throw new ScheduleException(
                $group,
                sprintf(
                    _('Schedule "%s" is not allowed at this time'),
                    $this->translator->getVariant($group->name)
                )
            );
        }
        // cannot be changed when payment exists
        if ($personSchedule && $personSchedule->getPayment()) {
            throw new ExistingPaymentException($personSchedule);
        }

        // check group capacity
        if (!$group->hasFreeCapacity()) {
            throw new FullCapacityException($item, $person, $this->translator);
        }
        // check item capacity
        if (isset($item->capacity) && ($item->capacity <= $item->getUsedCapacity(true))) {
            throw new FullCapacityException($item, $person, $this->translator);
        }
        $data = [
            'person_id' => $person->person_id,
            'schedule_item_id' => $value,
            'state' => PersonScheduleState::Applied,
        ];
        if (isset($definition['paymentDeadline'])) {
            $data['payment_deadline'] = $definition['paymentDeadline']->invoke($item);
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
        $this->service->disposeModel($personSchedule);
    }
}
