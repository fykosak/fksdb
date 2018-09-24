<?php

use FKSDB\ORM\ModelPerson;
use Nette\Security\IResource;

/**
 *
 * @author Michal Červeňák <miso@fykos.cz>
 * @property DateTime until
 * @property DateTime since
 * @property integer school_id
 * @property integer person_id
 */
class ModelTeacher extends AbstractModelSingle implements IResource {

    /**
     * @return ModelPerson
     */
    public function getPerson() {
        $data = $this->person;
        return ModelPerson::createFromTableRow($data);
    }

    /**
     * @return ModelSchool
     */
    public function getSchool() {
        $data = $this->school;
        return ModelSchool::createFromTableRow($data);
    }

    public function getResourceId() {
        return 'teacher';
    }

}
