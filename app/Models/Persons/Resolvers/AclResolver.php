<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons\Resolvers;

use FKSDB\Models\Authorization\Authorizators\Authorizator;
use FKSDB\Models\Authorization\Resource\ContestResourceHolder;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Persons\ResolutionMode;
use Nette\SmartObject;

class AclResolver implements Resolver
{
    use SmartObject;

    private Authorizator $authorizator;
    private ContestModel $contest;

    public function __construct(Authorizator $authorizator, ContestModel $contest)
    {
        $this->authorizator = $authorizator;
        $this->contest = $contest;
    }

    public function isVisible(?PersonModel $person): bool
    {
        return !$person || $this->isAllowed($person, 'edit');
    }

    public function getResolutionMode(?PersonModel $person): ResolutionMode
    {
        if (!$person) {
            return ResolutionMode::from(ResolutionMode::EXCEPTION);
        }
        return $this->isAllowed($person, 'edit') ? ResolutionMode::from(ResolutionMode::OVERWRITE)
            : ResolutionMode::from(ResolutionMode::EXCEPTION);
    }

    public function isModifiable(?PersonModel $person): bool
    {
        return !$person || $this->isAllowed($person, 'edit');
    }

    private function isAllowed(PersonModel $person, ?string $privilege): bool
    {
        return $this->authorizator->isAllowedContest(
            ContestResourceHolder::fromResource($person, $this->contest),
            $privilege,
            $this->contest
        );
    }
}
