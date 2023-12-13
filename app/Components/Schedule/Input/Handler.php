<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule\Input;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use FKSDB\Modules\Core\Language;
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
    public function saveGroup(PersonModel $person, ScheduleGroupModel $group, ?int $value): void
    {
        $personSchedule = $person->getScheduleByGroup($group);
        // already booked
        if ($personSchedule && $personSchedule->schedule_item_id === $value) {
            return;
        }
        if ($value) {
            if ($group->schedule_group_type->value === ScheduleGroupType::WeekendInfo) {
                throw new ScheduleException($group, _('Info block cannot be selected'));
            }
            /** @var ScheduleItemModel|null $item */
            $item = $group->getItems()->where('schedule_item_id', $value)->fetch();
            if (!$item) {
                throw new ScheduleException($group, sprintf(_('Item with Id %s does not exists'), $value));
            }
            if (!$item->available) {
                throw new ScheduleException(
                    $group,
                    sprintf(_('Item with Id %s is not available'), $item->name->getText($this->translator->lang))
                );
            }
            // create
            if ($personSchedule) {
                if (!$group->isModifiable()) {
                    throw new ScheduleException(
                        $group,
                        sprintf(
                            _('Schedule "%s" is not allowed at this time'),
                            $group->name->getText($this->translator->lang)
                        )
                    );
                }
                if ($personSchedule->getPayment()) {
                    throw new ExistingPaymentException($personSchedule);
                }
            } elseif (!$group->isModifiable()) {
                throw new ScheduleException(
                    $group,
                    sprintf(
                        _('Schedule "%s" is not available at this time'),
                        $group->name->getText($this->translator->lang)
                    )
                );
            } elseif (!$group->hasFreeCapacity()) {
                throw new FullCapacityException($item, $person, Language::from($this->translator->lang));
            }
            if (!$item->hasFreeCapacity()) {
                throw new FullCapacityException($item, $person, Language::from($this->translator->lang));
            }

            $this->service->storeModel(
                ['person_id' => $person->person_id, 'schedule_item_id' => $value],
                $personSchedule
            );
        } elseif ($personSchedule) {
            if (!$group->isModifiable()) {
                throw new ScheduleException($group, _('Modification of this item is not allowed at this time'));
            }
            if ($personSchedule->getPayment()) {
                throw new ExistingPaymentException($personSchedule);
            }
            $this->service->disposeModel($personSchedule);
        }
    }
}
