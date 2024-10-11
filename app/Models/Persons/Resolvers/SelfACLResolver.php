<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons\Resolvers;

use FKSDB\Models\Authorization\Authorizators\Authorizator;
use FKSDB\Models\Authorization\Resource\ContestResourceHolder;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Persons\ResolutionMode;
use Nette\DI\Container;
use Nette\Security\User;

class SelfACLResolver implements Resolver
{
    private ContestResourceHolder $resource;
    private string $privilege;
    private ContestModel $contest;
    private User $user;

    private Authorizator $authorizator;

    public function __construct(
        ContestResourceHolder $resource,
        string $privilege,
        ContestModel $contest,
        Container $container
    ) {
        $this->contest = $contest;
        $this->resource = $resource;
        $this->privilege = $privilege;
        $container->callInjects($this);
    }

    public function inject(Authorizator $authorizator, User $user): void
    {
        $this->authorizator = $authorizator;
        $this->user = $user;
    }


    public function isVisible(?PersonModel $person): bool
    {
        if (!$person) {
            return false;
        }
        if ($this->isSelf($person)) {
            return true;
        }
        if ($this->authorizator->isAllowedContest($this->resource, $this->privilege, $this->contest)) {
            return true;
        }
        return false;
    }


    public function getResolutionMode(?PersonModel $person): ResolutionMode
    {
        if (!$person) {
            return ResolutionMode::from(ResolutionMode::EXCEPTION);
        }
        if ($this->isSelf($person)) {
            return ResolutionMode::from(ResolutionMode::OVERWRITE);
        }
        if ($this->authorizator->isAllowedContest($this->resource, $this->privilege, $this->contest)) {
            return ResolutionMode::from(ResolutionMode::OVERWRITE);
        }
        return ResolutionMode::from(ResolutionMode::EXCEPTION);
    }

    public function isModifiable(?PersonModel $person): bool
    {
        if (!$person) {
            return false;
        }
        if ($this->isSelf($person)) {
            return true;
        }
        if ($this->authorizator->isAllowedContest($this->resource, $this->privilege, $this->contest)) {
            return true;
        }
        return false;
    }

    protected function isSelf(PersonModel $person): bool
    {
        if (!$this->user->isLoggedIn()) {
            return false;
        }
        /** @var LoginModel|null $login */
        $login = $this->user->getIdentity();
        $loggedPerson = $login->person;
        return $loggedPerson && $loggedPerson->person_id == $person->person_id;
    }
}
