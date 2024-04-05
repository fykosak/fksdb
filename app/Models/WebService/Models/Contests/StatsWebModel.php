<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Contests;

use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Modules\CoreModule\RestApiPresenter;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

/**
 * @phpstan-extends ContestYearWebModel<array{contestId:int,year:int},(SerializedTaskModel&TaskStatsType)[]>
 * @phpstan-import-type SerializedTaskModel from TaskModel
 * @phpstan-import-type TaskStatsType from TaskModel
 */
class StatsWebModel extends ContestYearWebModel
{
    protected function getExpectedParams(): Structure
    {
        return Expect::structure([
            'contestId' => Expect::scalar()->castTo('int'),
            'year' => Expect::scalar()->castTo('int')->required(),
        ]);
    }

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
            RestApiPresenter::RESOURCE_ID,
            self::class,
            $this->getContestYear()
        );
    }
}
