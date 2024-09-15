<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons\Resolvers;

use FKSDB\Models\Authorization\Authorizators\EventAuthorizator;
use FKSDB\Models\Authorization\Resource\EventResource;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Persons\ResolutionMode;
use Nette\DI\Container;
use Nette\Security\User;

class SelfACLEventResolver implements Resolver
{
    private EventResource $resource;
    private string $privilege;
    private EventModel $event;
    private User $user;

    private EventAuthorizator $eventAuthorizator;

    public function __construct(EventResource $resource, string $privilege, EventModel $event, Container $container)
    {
        $this->event = $event;
        $this->resource = $resource;
        $this->privilege = $privilege;
        $container->callInjects($this);
    }

    public function inject(EventAuthorizator $eventAuthorizator, User $user): void
    {
        $this->eventAuthorizator = $eventAuthorizator;
        $this->user = $user;
    }


    public function isVisible(?PersonModel $person): bool
    {
        if (!$person) {
            return false;
        }
        if (
            $this->eventAuthorizator->isAllowed($this->resource, $this->privilege, $this->event)
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
            $this->eventAuthorizator->isAllowed($this->resource, $this->privilege, $this->event)
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
            $this->eventAuthorizator->isAllowed($this->resource, $this->privilege, $this->event)
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
