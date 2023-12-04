<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\ORM\Services\ContestYearService;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

/**
 * @phpstan-extends WebModel<array{contest_id?:int,contestId:int,year:int},(SerializedTaskModel&TaskStatsType)[]>
 * @phpstan-import-type SerializedTaskModel from TaskModel
 * @phpstan-import-type TaskStatsType from TaskModel
 */
class StatsWebModel extends WebModel
{
    private ContestYearService $contestYearService;

    public function inject(ContestYearService $contestYearService): void
    {
        $this->contestYearService = $contestYearService;
    }

    public function getExpectedParams(): Structure
    {
        return Expect::structure([
            'contestId' => Expect::scalar()->castTo('int'),
            'contest_id' => Expect::scalar()->castTo('int'),
            'year' => Expect::scalar()->castTo('int')->required(),
        ]);
    }

    public function getJsonResponse(array $params): array
    {
        $contestYear = $this->contestYearService->findByContestAndYear(
            $params['contest_id'] ?? $params['contestId'],
            $params['year']
        );
        $contestYear->getTasks();
        $result = [];
        /** @var TaskModel $task */
        foreach ($contestYear->getTasks() as $task) {
            $result[] = array_merge($task->__toArray(), $task->getTaskStats());
        }
        return $result;
    }
}
