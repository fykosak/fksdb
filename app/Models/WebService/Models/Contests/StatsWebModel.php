<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Contests;

use FKSDB\Models\Authorization\Resource\PseudoContestYearResource;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Modules\CoreModule\RestApiPresenter;

/**
 * @phpstan-extends ContestYearWebModel<array{contestId:int,year:int},(SerializedTaskModel&TaskStatsType)[]>
 * @phpstan-import-type SerializedTaskModel from TaskModel
 * @phpstan-import-type TaskStatsType from TaskModel
 */
class StatsWebModel extends ContestYearWebModel
{
    /**
     * @throws NotFoundException
     */
    protected function getJsonResponse(): array
    {
        $result = [];
        /** @var TaskModel $task */
        foreach ($this->getContestYear()->getTasks() as $task) {
            $result[] = array_merge($task->__toArray(), $task->getTaskStats());
        }
        return $result;
    }

    /**
     * @throws NotFoundException
     */
    protected function isAuthorized(): bool
    {
        return $this->contestYearAuthorizator->isAllowed(
            new PseudoContestYearResource(RestApiPresenter::RESOURCE_ID, $this->getContestYear()),
            self::class,
            $this->getContestYear()
        );
    }
}
