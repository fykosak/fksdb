<?php

namespace FKSDB\Models\Persons;

use FKSDB\Models\Authorization\ContestAuthorizator;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelPerson;
use Nette\Security\Resource;
use Nette\SmartObject;

class AclResolver implements VisibilityResolver, ModifiabilityResolver {

    use SmartObject;

    private ContestAuthorizator $contestAuthorizator;

    private ModelContest $contest;

    public function __construct(ContestAuthorizator $contestAuthorizator, ModelContest $contest) {
        $this->contestAuthorizator = $contestAuthorizator;
        $this->contest = $contest;
    }

    public function isVisible(?ModelPerson $person): bool {
        return !$person || $this->isAllowed($person, 'edit');
    }

    public function getResolutionMode(?ModelPerson $person): string {
        if (!$person) {
            return ReferencedPersonHandler::RESOLUTION_EXCEPTION;
        }
        return $this->isAllowed($person, 'edit') ? ReferencedPersonHandler::RESOLUTION_OVERWRITE : ReferencedPersonHandler::RESOLUTION_EXCEPTION;
    }

    public function isModifiable(?ModelPerson $person): bool {
        return !$person || $this->isAllowed($person, 'edit');
    }

    /**
     * @param string|Resource|null $privilege
     */
    private function isAllowed(ModelPerson $person, $privilege): bool {
        return $this->contestAuthorizator->isAllowed($person, $privilege, $this->contest);
    }
}
