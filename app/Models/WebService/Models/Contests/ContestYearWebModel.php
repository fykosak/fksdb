<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Contests;

use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Services\ContestYearService;
use FKSDB\Models\WebService\Models\WebModel;
use Nette\Schema\Expect;

/**
 * @phpstan-template TParams of array{contestId:int,year:int}
 * @phpstan-template TReturn of array
 * @phpstan-extends WebModel<TParams,TReturn>
 */
abstract class ContestYearWebModel extends WebModel
{
    protected ContestYearModel $contestYear;
    private ContestYearService $contestYearService;

    public function inject(ContestYearService $contestYearService): void
    {
        $this->contestYearService = $contestYearService;
    }

    /**
     * @throws NotFoundException
     */
    protected function getContestYear(): ContestYearModel
    {
        if (!isset($this->contestYear)) {
            $contestYear = $this->contestYearService->findByContestAndYear(
                $this->params['contestId'],
                $this->params['year']
            );
            if (!$contestYear) {
                throw new NotFoundException();
            }
            $this->contestYear = $contestYear;
        }
        return $this->contestYear;
    }
    protected function getExpectedParams(): array
    {
        return [
            'contestId' => Expect::scalar()->castTo('int'),
            'year' => Expect::scalar()->castTo('int')->nullable(),
        ];
    }
}
