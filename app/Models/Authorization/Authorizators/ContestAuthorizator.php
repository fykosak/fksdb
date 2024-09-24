<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Authorizators;

use FKSDB\Models\Authorization\Resource\PseudoContestResource;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Services\ContestService;

final class ContestAuthorizator
{
    private Authorizator $authorizator;
    private ContestService $contestService;

    public function __construct(
        Authorizator $authorizator,
        ContestService $contestService
    ) {
        $this->authorizator = $authorizator;
        $this->contestService = $contestService;
    }

    public function isAllowedAnyContest(string $resource, ?string $privilege): bool
    {
        /** @var ContestModel $contest */
        foreach ($this->contestService->getTable() as $contest) {
            if ($this->authorizator->isAllowedContest(
                new PseudoContestResource($resource, $contest), $privilege, $contest)
            ) {
                return true;
            }
        }
        return $this->authorizator->isAllowedBase($resource, $privilege);
    }
}
