<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Authorizators;

use FKSDB\Models\Authorization\Resource\ContestResource;
use FKSDB\Models\Authorization\Resource\ContestYearResource;
use FKSDB\Models\Authorization\Resource\EventResource;
use FKSDB\Models\Authorization\Roles\Base\GuestRole;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\LoginModel;
use Nette\Security\Permission;
use Nette\Security\Resource;
use Nette\Security\User;

final class Authorizator
{
    private User $user;
    private Permission $permission;

    public function __construct(User $user, Permission $permission)
    {
        $this->user = $user;
        $this->permission = $permission;
    }

    public function isAllowedEvent(EventResource $resource, ?string $privilege, EventModel $context): bool
    {
        if ($context->event_id !== $resource->getEvent()->event_id) {
            return false;
        }
        /** @var LoginModel|null $login */
        $login = $this->user->getIdentity();
        if ($login) {
            foreach ($login->getEventRoles($context) as $role) {
                if ($this->permission->isAllowed($role, $resource, $privilege)) {
                    return true;
                }
            }
        }
        return $this->isAllowedContestYear(
            $resource,
            $privilege,
            $context->getContestYear()
        );
    }

    public function isAllowedContestYear(
        ContestYearResource $resource,
        ?string $privilege,
        ContestYearModel $context
    ): bool {
        if (
            $context->contest_id !== $resource->getContestYear()->contest_id
            || $context->year !== $resource->getContestYear()->year
        ) {
            return false;
        }
        /** @var LoginModel|null $login */
        $login = $this->user->getIdentity();
        if ($login) {
            foreach ($login->getImplicitContestYearRoles($context) as $role) {
                if ($this->permission->isAllowed($role, $resource, $privilege)) {
                    return true;
                }
            }
        }
        return $this->isAllowedContest(
            $resource,
            $privilege,
            $context->contest
        );
    }

    public function isAllowedContest(ContestResource $resource, ?string $privilege, ContestModel $context): bool
    {
        if ($context->contest_id !== $resource->getContest()->contest_id) {
            return false;
        }
        /** @var LoginModel|null $login */
        $login = $this->user->getIdentity();
        if ($login) {
            foreach ($login->getContestRoles($context) as $role) {
                if ($this->permission->isAllowed($role, $resource, $privilege)) {
                    return true;
                }
            }
        }
        return $this->isAllowedBase($resource, $privilege);
    }

    /**
     * @param Resource|string|null $resource
     */
    public function isAllowedBase($resource, ?string $privilege): bool
    {
        if (!$this->user->isLoggedIn()) {
            return $this->permission->isAllowed(new GuestRole(), $resource, $privilege);
        }
        /** @var LoginModel $login */
        $login = $this->user->identity;
        foreach ($login->getRoles() as $role) {
            if ($this->permission->isAllowed($role, $resource, $privilege)) {
                return true;
            }
        }
        return false;
    }
}
