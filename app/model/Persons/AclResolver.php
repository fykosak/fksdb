<?php

namespace Persons;

use Authorization\ContestAuthorizator;
use ModelContest;
use ModelPerson;
use Nette\Object;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class AclResolver extends Object implements IVisibilityResolver, IModifiabilityResolver {

    /**
     * @var ContestAuthorizator
     */
    private $contestAuthorizator;

    /**
     * @var ModelContest
     */
    private $contest;


    function __construct(ContestAuthorizator $contestAuthorizator, ModelContest $contest) {
        $this->contestAuthorizator = $contestAuthorizator;
        $this->contest = $contest;
    }

    public function isVisible(ModelPerson $person) {
        return $person->isNew() || $this->isAllowed($person, 'edit');
    }

    public function getResolutionMode(ModelPerson $person) {
        return $this->isAllowed($person, 'edit') ? ReferencedPersonHandler::RESOLUTION_OVERWRITE : ReferencedPersonHandler::RESOLUTION_EXCEPTION;
    }

    public function isModifiable(ModelPerson $person) {
        return $person->isNew() || $this->isAllowed($person, 'edit');
    }

    private function isAllowed(ModelPerson $person, $privilege) {
        return $this->contestAuthorizator->isAllowed($person, $privilege, $this->contest);
    }

}
