<?php

namespace Persons;

use Authorization\ContestAuthorizator;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelPerson;
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
     * @var \FKSDB\ORM\Models\ModelContest
     */
    private $contest;


    /**
     * AclResolver constructor.
     * @param ContestAuthorizator $contestAuthorizator
     * @param \FKSDB\ORM\Models\ModelContest $contest
     */
    function __construct(ContestAuthorizator $contestAuthorizator, ModelContest $contest) {
        $this->contestAuthorizator = $contestAuthorizator;
        $this->contest = $contest;
    }

    /**
     * @param ModelPerson $person
     * @return bool|mixed
     */
    public function isVisible(ModelPerson $person) {
        return $person->isNew() || $this->isAllowed($person, 'edit');
    }

    /**
     * @param \FKSDB\ORM\Models\ModelPerson $person
     * @return mixed|string
     */
    public function getResolutionMode(ModelPerson $person) {
        return $this->isAllowed($person, 'edit') ? ReferencedPersonHandler::RESOLUTION_OVERWRITE : ReferencedPersonHandler::RESOLUTION_EXCEPTION;
    }

    /**
     * @param \FKSDB\ORM\Models\ModelPerson $person
     * @return bool|mixed
     */
    public function isModifiable(ModelPerson $person) {
        return $person->isNew() || $this->isAllowed($person, 'edit');
    }

    /**
     * @param ModelPerson $person
     * @param $privilege
     * @return bool
     */
    private function isAllowed(ModelPerson $person, $privilege) {
        return $this->contestAuthorizator->isAllowed($person, $privilege, $this->contest);
    }

}
