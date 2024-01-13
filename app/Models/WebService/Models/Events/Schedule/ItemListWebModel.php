<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Events\Schedule;

use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Services\Schedule\ScheduleGroupService;
use FKSDB\Models\WebService\Models\WebModel;
use Nette\Application\BadRequestException;
use Nette\Http\IResponse;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

/**
 * @phpstan-import-type SerializedScheduleItemModel from ScheduleItemModel
 * @phpstan-extends WebModel<array{groupId:int},SerializedScheduleItemModel[]>
 */
class ItemListWebModel extends WebModel
{
    private ScheduleGroupService $scheduleGroupService;

    public function inject(ScheduleGroupService $scheduleGroupService): void
    {
        $this->scheduleGroupService = $scheduleGroupService;
    }

    protected function getExpectedParams(): Structure
    {
        return Expect::structure([
            'groupId' => Expect::scalar()->castTo('int')->required(),
        ]);
    }

    /**
     * @throws BadRequestException
     * @throws \Exception
     */
    protected function getJsonResponse(array $params): array
    {
        $group = $this->scheduleGroupService->findByPrimary($params['groupId']);
        if (!$group) {
            throw new BadRequestException('Unknown group.', IResponse::S404_NOT_FOUND);
        }
        $data = [];
        /** @var ScheduleItemModel $item */
        foreach ($group->getItems() as $item) {
            $data[] = $item->__toArray();
        }
        return $data;
    }

    protected function isAuthorized(array $params): bool
    {
        return false;
    }
}
