<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Services\ContestService;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

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
class ContestsModel extends WebModel
{
    private ContestService $contestService;

    public function inject(ContestService $contestService): void
    {
        $this->contestService = $contestService;
    }

    public function getJsonResponse(array $params): array
    {
        $data = [];
        /** @var ContestModel $contest */
        foreach ($this->contestService->getTable() as $contest) {
            $data[] = $contest->__toArray();
        }
        return $data;
    }

    public function getExpectedParams(): Structure
    {
        return Expect::structure([]);
    }
}
