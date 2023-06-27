<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\Schedule;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use Fykosak\Utils\Localization\GettextTranslator;
use Nette\DI\Container;

class Handler
{
    private PersonScheduleService $service;
    private GettextTranslator $translator;

    public function __construct(Container $container)
    {
        $container->callInjects($this);
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
     */
    public function handle(array $data, PersonModel $person, EventModel $event): void
    {
        foreach ($data as $type => $items) {
            foreach ($items as $groupId => $item) {
                /** @var ScheduleGroupModel|null $group */
                $group = $event->getScheduleGroups()
                    ->where('schedule_group_type', $type)
                    ->where('schedule_group_id', $groupId)
                    ->fetch();
                if (!$group) {
                    throw new ScheduleException(null, _('Schedule group does not exists'));
                }
                $this->saveGroup($person, $group, (int)$item);
            }
        }
    }
    public function saveGroup(PersonModel $person, ScheduleGroupModel $group, ?int $value): void
    {
        $personSchedule = $person->getScheduleByGroup($group);
        // already booked
        if ($personSchedule && $personSchedule->schedule_item_id === $value) {
            return;
        }
        if ($value) {
            /** @var ScheduleItemModel|null $item */
            $item = $group->getItems()->where('schedule_item_id', $value)->fetch();
            if (!$item) {
                throw new ScheduleException($group, sprintf(_('Item with Id %s does not exists'), $value));
            }
            // create
            if ($personSchedule) {
                if (!$group->canEdit()) {
                    throw new ScheduleException($group, _('Modification of this item is not allowed at this time'));
                }
                if ($personSchedule->getPayment()) {
                    throw new ExistingPaymentException($personSchedule);
                }
            } elseif (!$group->canCreate()) {
                throw new ScheduleException($group, _('Given item is not available at this time'));
            } elseif (!$group->hasFreeCapacity()) {
                throw new FullCapacityException($item, $person, $this->translator->lang);
            }
            if (!$item->hasFreeCapacity()) {
                throw new FullCapacityException($item, $person, $this->translator->lang);
            }

            $this->service->storeModel(
                ['person_id' => $person->person_id, 'schedule_item_id' => $value],
                $personSchedule
            );
        } elseif ($personSchedule) {
            if (!$group->canEdit()) {
                throw new ScheduleException($group, _('Modification of this item is not allowed at this time'));
            }
            if ($personSchedule->getPayment()) {
                throw new ExistingPaymentException($personSchedule);
            }
            $this->service->disposeModel($personSchedule);
        }
    }
}
