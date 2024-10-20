<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Input;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleState;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use FKSDB\Models\Transitions\Machine\PersonScheduleMachine;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\Localization\GettextTranslator;
use Nette\DI\Container;

class Handler
{
    private PersonScheduleService $service;
    private GettextTranslator $translator;

    private PersonScheduleMachine $machine;

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
     * @phpstan-param array<string,array<string,array<int,int>>> $data
     */
    public function handle(array $data, PersonModel $person, EventModel $event): void
    {
        foreach ($data as $dataGroup) {
            foreach ($dataGroup as $dayGroup) {
                foreach ($dayGroup as $groupId => $itemId) {
                    /** @var ScheduleGroupModel|null $group */
                    $group = $event->getScheduleGroups()
                        ->where('schedule_group_id', $groupId)
                        ->fetch();
                    if (!$group) {
                        throw new ScheduleException(null, _('Schedule group does not exists'));
                    }
                    $this->saveGroup($person, $group, $itemId);
                }
            }
        }
    }

    /**
     * @throws \Throwable
     */
    public function saveGroup(PersonModel $person, ScheduleGroupModel $group, ?int $value): void
    {
        $personSchedule = $person->getScheduleByGroup($group);
        // already booked
        if ($personSchedule && $personSchedule->schedule_item_id === $value) {
            return;
        }
        if ($value) {
            // može skupiny vybraný?
            if (!$group->schedule_group_type->isSelectable()) {
                throw new ScheduleException($group, _('Info block cannot be selected'));
            }
            // existuje to ID a je v danej grupe
            /** @var ScheduleItemModel|null $item */
            $item = $group->getItems()->where('schedule_item_id', $value)->fetch();
            if (!$item) {
                throw new ScheduleException($group, sprintf(_('Item with Id %s does not exists'), $value));
            }
            // je item dostaupný cez GUI?
            if (!$item->available) {
                throw new ScheduleException(
                    $group,
                    sprintf(_('Item with Id %s is not available'), $item->name->getText($this->translator->lang))
                );
            }
            // dá sa ešte/už meniť skipina?
            if (!$group->isModifiable()) {
                throw new ScheduleException(
                    $group,
                    sprintf(
                        _('Schedule "%s" is not allowed at this time'),
                        $group->name->getText($this->translator->lang)
                    )
                );
            }
            // má položka vytvorenú platbu?
            if ($personSchedule) {
                if ($personSchedule->getPayment()) {
                    throw new ExistingPaymentException($personSchedule);
                }
            } elseif (!$group->hasFreeCapacity()) {
                throw new FullCapacityException($item, $person, $this->translator);
            }
            if (isset($item->capacity) && ($item->capacity <= $item->getUsedCapacity(true))) {
                throw new FullCapacityException($item, $person, $this->translator);
            }

            $this->service->storeModel(
                ['person_id' => $person->person_id, 'schedule_item_id' => $value],
                $personSchedule
            );
        } elseif ($personSchedule) {
            $this->cancel($personSchedule);
        }
    }

    /**
     * @throws \Throwable
     */
    private function cancel(PersonScheduleModel $personSchedule): void
    {
        $holder = $this->machine->createHolder($personSchedule);
        $transition = $this->machine->getTransitions()
            ->filterByTarget(PersonScheduleState::from(PersonScheduleState::Cancelled))
            ->filterAvailable($holder)
            ->select();
        $transition->execute($holder);
    }
}
