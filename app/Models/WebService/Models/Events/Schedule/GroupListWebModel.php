<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Events\Schedule;

use FKSDB\Models\Authorization\Resource\PseudoEventResource;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\WebService\Models\Events\EventWebModel;
use FKSDB\Modules\CoreModule\RestApiPresenter;
use Nette\Application\BadRequestException;
use Nette\Schema\Expect;

/**
 * @phpstan-type SerializedScheduleItemModel array{
 *      groupId:int,
 *      itemId:int,
 *      price:array<string, string>,
 *      capacity:array{
 *          total:int|null,
 *          used:int|null,
 *      },
 *      name:array<string, string>,
 *      begin:\DateTimeInterface,
 *      end:\DateTimeInterface,
 *      available: bool,
 *      description:array<string, string>,
 *      longDescription:array<string, string>,
 * }
 * @phpstan-type SerializedScheduleGroupModel array{
 *      groupId:int,
 *      type:string,
 *      registration:array{begin:string|null,end:string|null,},
 *      name:array<string, string>,
 *      eventId:int,
 *      start:string,
 *      end:string,
 *      items: SerializedScheduleItemModel[],
 * }
 * @phpstan-extends EventWebModel<array{eventId:int,types:string[]},SerializedScheduleGroupModel[]>
 */
class GroupListWebModel extends EventWebModel
{
    protected function getExpectedParams(): array
    {
        return array_merge(
            parent::getExpectedParams(),
            [
                'types' => Expect::listOf(
                    Expect::anyOf(
                        ...array_map(fn(ScheduleGroupType $type): string => $type->value, ScheduleGroupType::cases())
                    )
                )->default([])->required(false),
            ]
        );
    }

    /**
     * @throws BadRequestException
     * @throws \Exception
     */
    protected function getJsonResponse(): array
    {
        $data = [];
        $query = $this->getEvent()->getScheduleGroups();
        if (count($this->params['types'])) {
            $query->where('schedule_group_type', $this->params['types']);
        }
        /** @var ScheduleGroupModel $group */
        foreach ($query as $group) {
            $items = [];
            /** @var ScheduleItemModel $item */
            foreach ($group->getItems() as $item) {
                $items[$item->schedule_item_id] = [
                    'groupId' => $item->schedule_group_id,
                    'itemId' => $item->schedule_item_id,
                    'price' => $item->getPrice()->__serialize(),
                    'capacity' => [
                        'total' => $item->capacity,
                        'used' => $item->getUsedCapacity(),
                    ],
                    'name' => $item->name->__serialize(),
                    'begin' => $item->getBegin(),
                    'end' => $item->getEnd(),
                    'description' => $item->description->__serialize(),
                    'longDescription' => $item->long_description->__serialize(),
                    'available' => (bool)$item->available,
                ];
            }
            $data[$group->schedule_group_id] = [
                'groupId' => $group->schedule_group_id,
                'type' => $group->schedule_group_type->value,
                'registration' => [
                    'begin' => $group->registration_begin ? $group->registration_begin->format('c') : null,
                    'end' => $group->registration_end ? $group->registration_end->format('c') : null,
                ],
                'name' => $group->name->__serialize(),
                'eventId' => $group->event_id,
                'start' => $group->start->format('c'),
                'end' => $group->end->format('c'),
                'items' => $items,
            ];
        }
        return $data;
    }

    /**
     * @throws NotFoundException
     */
    protected function isAuthorized(): bool
    {
        return $this->authorizator->isAllowedEvent(
            new PseudoEventResource(RestApiPresenter::RESOURCE_ID, $this->getEvent()),
            self::class,
            $this->getEvent()
        );
    }
}
