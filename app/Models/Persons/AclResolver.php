<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons;

use FKSDB\Models\Authorization\ContestAuthorizator;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\PersonModel;
use Nette\Security\Resource;
use Nette\SmartObject;

class AclResolver implements VisibilityResolver, ModifiabilityResolver
{
    use SmartObject;

    private ContestAuthorizator $contestAuthorizator;

    private ContestModel $contest;

    public function __construct(ContestAuthorizator $contestAuthorizator, ContestModel $contest)
    {
        $this->contestAuthorizator = $contestAuthorizator;
        $this->contest = $contest;
    }

    public function isVisible(?PersonModel $person): bool
    {
        return !$person || $this->isAllowed($person, 'edit');
    }

    public function getResolutionMode(?PersonModel $person): string
    {
        if (!$person) {
            return ReferencedHandler::RESOLUTION_EXCEPTION;
        }
        return $this->isAllowed($person, 'edit') ? ReferencedHandler::RESOLUTION_OVERWRITE
            : ReferencedHandler::RESOLUTION_EXCEPTION;
    }

    public function isModifiable(?PersonModel $person): bool
    {
        return !$person || $this->isAllowed($person, 'edit');
    }

    /**
     * @param string|Resource|null $privilege
     */
    private function isAllowed(PersonModel $person, $privilege): bool
    {
        return $this->contestAuthorizator->isAllowed($person, $privilege, $this->contest);
    }
}
