<?php

declare(strict_types=1);

namespace FKSDB\Tests\ModelsTests\Schedule;

use FKSDB\Components\Schedule\Input\ScheduleHandler;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use FKSDB\Models\ORM\Services\Schedule\ScheduleGroupService;
use FKSDB\Models\ORM\Services\Schedule\ScheduleItemService;
use FKSDB\Tests\ModelsTests\DatabaseTestCase;
use Fykosak\Utils\Localization\GettextTranslator;

abstract class HandlerTestCase extends DatabaseTestCase
{
    protected ScheduleHandler $handler;
    protected PersonModel $tester;
    protected ScheduleGroupModel $group;

    protected ScheduleItemModel $item1;
    protected ScheduleItemModel $item2;
    protected ScheduleItemModel $item3;

    protected PersonScheduleService $personScheduleService;
    protected ScheduleItemService $itemService;
    protected ScheduleGroupService $groupService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container->getByType(GettextTranslator::class)->setLang('cs');
        $this->personScheduleService = $this->container->getByType(PersonScheduleService::class);
        $this->itemService = $this->container->getByType(ScheduleItemService::class);
        $this->groupService = $this->container->getByType(ScheduleGroupService::class);

        $this->tester = $this->createPerson('Tester', 'testorovič');
        /** @var EventModel $event */
        $event = $this->container->getByType(EventService::class)->storeModel([
            'event_type_id' => 1,
            'year' => 1,
            'event_year' => 1,
            'begin' => new \DateTime(),
            'end' => new \DateTime(),
            'registration_begin' => (new \DateTime())->sub(new \DateInterval('P1D')),
            'registration_end' => (new \DateTime())->add(new \DateInterval('P1D')),
            'name' => 'Test FOL opened',
        ]);
        $this->handler = new ScheduleHandler($this->container, $event);
        $this->group = $this->groupService->storeModel([
            'schedule_group_type' => 'accommodation',
            'name_cs' => 'name CS',
            'name_en' => 'name EN',
            'event_id' => $event->event_id,
            'start' => new \DateTime(),
            'end' => new \DateTime(),
        ]);


        $this->item1 = $this->itemService->storeModel([
            'name_cs' => 'item1',
            'name_en' => 'item1',
            'schedule_group_id' => $this->group->schedule_group_id,
            'capacity' => 5,
        ]);

        $this->item2 = $this->itemService->storeModel([
            'name_cs' => 'item2',
            'name_en' => 'item2',
            'schedule_group_id' => $this->group->schedule_group_id,
            'capacity' => 5,
        ]);
        $this->item3 = $this->itemService->storeModel([
            'name_cs' => 'item3',
            'name_en' => 'item3',
            'schedule_group_id' => $this->group->schedule_group_id,
            'capacity' => 5,
        ]);
    }

    protected function personToItem(ScheduleItemModel $item, int $personCount): void
    {
        for ($i = 0; $i < $personCount; $i++) {
            $key = 'random-' . $item->schedule_item_id . '-' . $i;
            $person = $this->createPerson($key, $key);
            $this->personScheduleService->storeModel([
                'person_id' => $person->person_id,
                'schedule_item_id' => $item->schedule_item_id,
            ]);
        }
    }
}
