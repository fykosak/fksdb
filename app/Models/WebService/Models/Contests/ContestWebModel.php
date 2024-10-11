<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Contests;

use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Services\ContestService;
use FKSDB\Models\WebService\Models\WebModel;
use Nette\Schema\Expect;

/**
 * @phpstan-template TParams of array{contestId:int}
 * @phpstan-template TReturn of array
 * @phpstan-extends WebModel<TParams,TReturn>
 */
abstract class ContestWebModel extends WebModel
{
    protected ContestModel $contest;
    protected ContestService $contestService;

    public function inject(ContestService $contestService): void
    {
        $this->contestService = $contestService;
    }

    /**
     * @throws NotFoundException
     */
    protected function getContest(): ContestModel
    {
        if (!isset($this->contest)) {
            $contest = $this->contestService->findByPrimary($this->params['contestId']);
            if (!$contest) {
                throw new NotFoundException();
            }
            $this->contest = $contest;
        }
        return $this->contest;
    }
    protected function getExpectedParams(): array
    {
        return [
            'contestId' => Expect::scalar()->castTo('int'),
        ];
    }
}
