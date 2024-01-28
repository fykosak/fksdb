<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Contests;

use FKSDB\Models\ORM\Models\TaskModel;
use FKSDB\Models\ORM\Services\ContestYearService;
use FKSDB\Models\WebService\Models\WebModel;
use FKSDB\Modules\CoreModule\RestApiPresenter;
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

    protected function getExpectedParams(): Structure
    {
        return Expect::structure([
            'contestId' => Expect::scalar()->castTo('int'),
            'contest_id' => Expect::scalar()->castTo('int'),
            'year' => Expect::scalar()->castTo('int')->required(),
        ]);
    }

    protected function getJsonResponse(): array
    {
        $contestYear = $this->contestYearService->findByContestAndYear(
            $this->params['contest_id'] ?? $this->params['contestId'],
            $this->params['year']
        );
        $contestYear->getTasks();
        $result = [];
        /** @var TaskModel $task */
        foreach ($contestYear->getTasks() as $task) {
            $result[] = array_merge($task->__toArray(), $task->getTaskStats());
        }
        return $result;
    }

    protected function isAuthorized(): bool
    {
        $contestYear = $this->contestYearService->findByContestAndYear(
            $this->params['contest_id'] ?? $this->params['contestId'],
            $this->params['year']
        );
        return $this->contestYearAuthorizator->isAllowed(RestApiPresenter::RESOURCE_ID, self::class, $contestYear);
    }
}
