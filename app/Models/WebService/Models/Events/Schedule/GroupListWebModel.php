<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Events\Schedule;

use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\WebService\Models\WebModel;
use Nette\Application\BadRequestException;
use Nette\Http\IResponse;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

/**
 * @phpstan-import-type SerializedScheduleGroupModel from ScheduleGroupModel
 * @phpstan-extends WebModel<array{eventId:int,types:string[]},SerializedScheduleGroupModel[]>
 */
class GroupListWebModel extends WebModel
{
    private EventService $eventService;

    public function inject(EventService $eventService): void
    {
        $this->eventService = $eventService;
    }

    public function getExpectedParams(): Structure
    {
        return Expect::structure([
            'eventId' => Expect::scalar()->castTo('int')->required(),
            'types' => Expect::listOf(
                Expect::anyOf(
                    ...array_map(fn(ScheduleGroupType $type): string => $type->value, ScheduleGroupType::cases())
                )
            )->default([])->required(false),
        ]);
    }

    /**
     * @throws BadRequestException
     * @throws \Exception
     */
    protected function getJsonResponse(array $params): array
    {
        $event = $this->eventService->findByPrimary($params['eventId']);
        if (!$event) {
            throw new BadRequestException('Unknown event.', IResponse::S404_NOT_FOUND);
        }
        $data = [];
        $query = $event->getScheduleGroups();
        if (count($params['types'])) {
            $query->where('schedule_group_type', $params['types']);
        }
        /** @var ScheduleGroupModel $group */
        foreach ($query as $group) {
            $data[] = $group->__toArray();
        }
        return $data;
    }
}
