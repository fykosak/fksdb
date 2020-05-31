<?php

namespace Persons;

use Authorization\ContestAuthorizator;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Security\IResource;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class AclResolver implements IVisibilityResolver, IModifiabilityResolver {
    use SmartObject;

    private ContestAuthorizator $contestAuthorizator;

    private ModelContest $contest;

    /**
     * AclResolver constructor.
     * @param ContestAuthorizator $contestAuthorizator
     * @param ModelContest $contest
     */
    public function __construct(ContestAuthorizator $contestAuthorizator, ModelContest $contest) {
        $this->contestAuthorizator = $contestAuthorizator;
        $this->contest = $contest;
    }

    public function isVisible(ModelPerson $person): bool {
        return $person->isNew() || $this->isAllowed($person, 'edit');
    }

    public function getResolutionMode(ModelPerson $person): string {
        return $this->isAllowed($person, 'edit') ? ReferencedPersonHandler::RESOLUTION_OVERWRITE : ReferencedPersonHandler::RESOLUTION_EXCEPTION;
    }

    public function isModifiable(ModelPerson $person): bool {
        return $person->isNew() || $this->isAllowed($person, 'edit');
    }

    /**
     * @param ModelPerson $person
     * @param string|IResource $privilege
     * @return bool
     */
    private function isAllowed(ModelPerson $person, $privilege): bool {
        return $this->contestAuthorizator->isAllowed($person, $privilege, $this->contest);
    }
}
