<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Authorizators;

use FKSDB\Models\Authorization\Resource\BaseResourceHolder;
use FKSDB\Models\Authorization\Resource\ContestResourceHolder;
use FKSDB\Models\Authorization\Resource\ContestYearResourceHolder;
use FKSDB\Models\Authorization\Resource\EventResourceHolder;
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
    private ?LoginModel $login;
    private Permission $permission;

    public function __construct(User $user, Permission $permission)
    {
        /** @phpstan-ignore-next-line */
        $this->login = $user->getIdentity();
        $this->permission = $permission;
    }

    public function isAllowedEvent(EventResourceHolder $resource, ?string $privilege, EventModel $context): bool
    {
        if ($context->event_id !== $resource->getContext()->event_id) {
            return false;
        }
        return $this->innerAllowedEvent($resource, $privilege, $context);
    }

    public function isAllowedContestYear(
        ContestYearResourceHolder $resource,
        ?string $privilege,
        ContestYearModel $context
    ): bool {
        if ($context->contest_id !== $resource->getContext()->contest_id
            || $context->year !== $resource->getContext()->year) {
            return false;
        }
        return $this->innerAllowedContestYear($resource, $privilege, $context);
    }

    public function isAllowedContest(ContestResourceHolder $resource, ?string $privilege, ContestModel $context): bool
    {
        if ($context->contest_id !== $resource->getContext()->contest_id) {
            return false;
        }

        return $this->innerAllowedContest($resource, $privilege, $context);
    }

    public function isAllowedBase(BaseResourceHolder $resource, ?string $privilege): bool
    {
        return $this->innerAllowedBase($resource, $privilege);
    }

    private function innerAllowedEvent(
        Resource $resource,
        ?string $privilege,
        EventModel $context
    ): bool {
        if ($this->login) {
            foreach ($this->login->getEventRoles($context->getEvent()) as $role) {
                if ($this->permission->isAllowed($role, $resource, $privilege)) {
                    return true;
                }
            }
        }
        return $this->innerAllowedContestYear($resource, $privilege, $context->getContestYear());
    }

    private function innerAllowedContestYear(
        Resource $resource,
        ?string $privilege,
        ContestYearModel $context
    ): bool {
        if ($this->login) {
            foreach ($this->login->getContestYearRoles($context->getContestYear()) as $role) {
                if ($this->permission->isAllowed($role, $resource, $privilege)) {
                    return true;
                }
            }
        }
        return $this->innerAllowedContest(
            $resource,
            $privilege,
            $context->contest
        );
    }

    private function innerAllowedContest(
        Resource $resource,
        ?string $privilege,
        ContestModel $context
    ): bool {
        if ($this->login) {
            foreach ($this->login->getContestRoles($context->getContest()) as $role) {
                if ($this->permission->isAllowed($role, $resource, $privilege)) {
                    return true;
                }
            }
        }
        return $this->innerAllowedBase($resource, $privilege);
    }

    private function innerAllowedBase(
        Resource $resource,
        ?string $privilege
    ): bool {
        if (!$this->login) {
            return $this->permission->isAllowed(new GuestRole(), $resource, $privilege);
        }
        foreach ($this->login->getRoles() as $role) {
            if ($this->permission->isAllowed($role, $resource, $privilege)) {
                return true;
            }
        }
        return false;
    }
}
