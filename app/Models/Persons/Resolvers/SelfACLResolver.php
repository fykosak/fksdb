<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons\Resolvers;

use FKSDB\Models\Authorization\ContestAuthorizator;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Persons\ResolutionMode;
use Nette\DI\Container;
use Nette\Security\Resource;
use Nette\Security\User;

class SelfACLResolver implements Resolver
{
    /** @var Resource|string */
    private $resource;
    private string $privilege;
    private ContestModel $contest;
    private User $user;

    private ContestAuthorizator $contestAuthorizator;

    /**
     * @param string|Resource $resource
     */
    public function __construct($resource, string $privilege, ContestModel $contest, Container $container)
    {
        $this->contest = $contest;
        $this->resource = $resource;
        $this->privilege = $privilege;
        $container->callInjects($this);
    }

    public function inject(ContestAuthorizator $contestAuthorizator, User $user): void
    {
        $this->contestAuthorizator = $contestAuthorizator;
        $this->user = $user;
    }


    public function isVisible(?PersonModel $person): bool
    {
        if (!$person) {
            return false;
        }
        if (
            $this->contestAuthorizator->isAllowed($this->resource, $this->privilege, $this->contest)
            || $this->isSelf($person)
        ) {
            return true;
        }
        return false;
    }


    public function getResolutionMode(?PersonModel $person): ResolutionMode
    {
        if (!$person) {
            return ResolutionMode::from(ResolutionMode::EXCEPTION);
        }
        if (
            $this->contestAuthorizator->isAllowed($this->resource, $this->privilege, $this->contest)
            || $this->isSelf($person)
        ) {
            return ResolutionMode::from(ResolutionMode::OVERWRITE);
        }
        return ResolutionMode::from(ResolutionMode::EXCEPTION);
    }

    public function isModifiable(?PersonModel $person): bool
    {
        if (!$person) {
            return false;
        }
        if (
            $this->contestAuthorizator->isAllowed($this->resource, $this->privilege, $this->contest)
            || $this->isSelf($person)
        ) {
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
