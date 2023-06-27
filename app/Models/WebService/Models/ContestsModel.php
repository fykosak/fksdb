<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Services\ContestService;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

class ContestsModel extends WebModel
{
    private ContestService $contestService;

    public function inject(ContestService $contestService): void
    {
        $this->contestService = $contestService;
    }

    private function createContestArray(ContestModel $contest): array
    {
        return [
            "contestId" => $contest->contest_id,
            "contest" => $contest->getContestSymbol(),
            "name" => $contest->name,
            "currentYear" => $contest->getCurrentContestYear()->year,
            "firstYear" => $contest->getFirstYear(),
            "lastYear" => $contest->getLastYear(),
        ];
    }

    public function getJsonResponse(array $params): array
    {
        $data = [];
        /** @var ContestModel $contest */
        foreach ($this->contestService->getTable() as $contest) {
            $data[] = $this->createContestArray($contest);
        }
        return $data;
    }

    public function getExpectedParams(): Structure
    {
        return Expect::structure([]);
    }
}
