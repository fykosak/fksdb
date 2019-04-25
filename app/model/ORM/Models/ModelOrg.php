<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Database\Table\ActiveRow;
use Nette\Security\IResource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read ActiveRow contest
 * @property-read ActiveRow person
 * @property-read int since
 * @property-read int contest_id
 * @property-read int|null until
 * @property-read int org_id
 * @property-read int person_id
 * @property-read string role
 * @property-read int order
 * @property-read string contribution
 * @property-read string tex_signature
 * @property-read string domain_alias
 */
class ModelOrg extends AbstractModelSingle implements IResource, IPersonReferencedModel, IContestReferencedModel {

    /**
     * @return ModelContest
     */
    public function getContest(): ModelContest {
        return ModelContest::createFromActiveRow($this->contest);
    }

    /**
     * @return ModelPerson
     */
    public function getPerson(): ModelPerson {
        return ModelPerson::createFromActiveRow($this->person);
    }

    /**
     * @return string
     */
    public function getResourceId(): string {
        return 'org';
    }

}
