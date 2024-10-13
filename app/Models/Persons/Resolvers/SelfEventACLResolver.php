<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons\Resolvers;

use FKSDB\Models\Authorization\Authorizators\Authorizator;
use FKSDB\Models\Authorization\Resource\EventResourceHolder;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Persons\ResolutionMode;
use Nette\DI\Container;
use Nette\Security\User;

class SelfEventACLResolver implements Resolver
{
    private EventResourceHolder $resource;
    private string $privilege;
    private EventModel $event;
    private User $user;

    private Authorizator $authorizator;

    public function __construct(
        EventResourceHolder $resource,
        string $privilege,
        EventModel $event,
        Container $container
    ) {
        $this->event = $event;
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
        if ($this->authorizator->isAllowedEvent($this->resource, $this->privilege, $this->event)) {
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
        if ($this->authorizator->isAllowedEvent($this->resource, $this->privilege, $this->event)) {
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
        if ($this->authorizator->isAllowedEvent($this->resource, $this->privilege, $this->event)) {
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
