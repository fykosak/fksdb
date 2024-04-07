<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Contests;

use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Services\ContestService;
use FKSDB\Models\WebService\Models\WebModel;
use FKSDB\Modules\CoreModule\RestApiPresenter;

/**
 * @phpstan-extends WebModel<array<never>,array<int,array{
 *      contestId:int,
 *      contest:string,
 *      name:string,
 *      currentYear:int,
 *      firstYear:int,
 *      lastYear:int,
 * }>>
 */
class ContestsWebModel extends WebModel
{
    private ContestService $contestService;

    public function inject(ContestService $contestService): void
    {
        $this->contestService = $contestService;
    }

    protected function getJsonResponse(): array
    {
        $data = [];
        /** @var ContestModel $contest */
        foreach ($this->contestService->getTable() as $contest) {
            if ($this->contestAuthorizator->isAllowed(RestApiPresenter::RESOURCE_ID, self::class, $contest)) {
                $data[] = $contest->__toArray();
            }
        }
        return $data;
    }

    protected function getInnerStructure(): array
    {
        return [];
    }

    protected function isAuthorized(): bool
    {
        return $this->contestAuthorizator->isAllowedAnyContest(RestApiPresenter::RESOURCE_ID, self::class);
    }
}
