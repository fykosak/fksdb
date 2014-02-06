<?php

namespace Persons;

use Authorization\ContestAuthorizator;
use ModelContest;
use ModelPerson;
use Nette\Object;
use Nette\Security\IResource;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class AclResolver extends Object implements IVisibilityResolver, IModifialibityResolver {

    /**
     * @var ContestAuthorizator
     */
    private $contestAuthorizator;

    /**
     * @var ModelContest
     */
    private $contest;

    /**
     * @var IResource|string
     */
    private $resource;

    function __construct(ContestAuthorizator $contestAuthorizator, ModelContest $contest, $resource) {
        $this->contestAuthorizator = $contestAuthorizator;
        $this->contest = $contest;
        $this->resource = $resource;
    }

    public function isVisible(ModelPerson $person) {
        return $person->isNew() || $this->isAllowed('edit');
    }

    public function getResolutionMode(ModelPerson $person) {
        return $this->isAllowed('edit') ? ReferencedPersonHandler::RESOLUTION_OVERWRITE : ReferencedPersonHandler::RESOLUTION_EXCEPTION;
    }

    public function isModifiable(ModelPerson $person) {
        return $person->isNew() || $this->isAllowed('edit');
    }

    private function isAllowed($privilege) {
        return $this->contestAuthorizator->isAllowed($this->resource, $privilege, $this->contest);
    }

}
