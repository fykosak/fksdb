<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Contests;

use FKSDB\Models\Authorization\Resource\ContestResourceHolder;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Modules\CoreModule\RestApiPresenter;

/**
 * @phpstan-extends ContestWebModel<array{contestId:int},array{
 *      contestId:int,
 *      contest:string,
 *      name:string,
 *      years:array<array{
 *          year:int,
 *          active:bool,
 *          begin:string,
 *          end:string,
 *      }>
 * }>
 */
class ContestsWebModel extends ContestWebModel
{
    /**
     * @throws NotFoundException
     */
    protected function getJsonResponse(): array
    {
        $contest = $this->getContest();
        $datum = [
            'contestId' => $contest->contest_id,
            'contest' => $contest->getContestSymbol(),
            'name' => $contest->name,
        ];
        $datum['years'] = [];
        /** @var ContestYearModel $contestYear */
        foreach ($contest->getContestYears() as $contestYear) {
            $datum['years'][] = [
                'year' => $contestYear->year,
                'active' => $contestYear->isActive(),
                'begin' => $contestYear->begin()->format('c'),
                'end' => $contestYear->end()->format('c'),
            ];
        }
        return $datum;
    }

    protected function getExpectedParams(): array
    {
        return [];
    }

    /**
     * @throws NotFoundException
     */
    protected function isAuthorized(): bool
    {
        return $this->authorizator->isAllowedContest(
            ContestResourceHolder::fromResourceId(RestApiPresenter::RESOURCE_ID, $this->getContest()),
            self::class,
            $this->getContest()
        );
    }
}
