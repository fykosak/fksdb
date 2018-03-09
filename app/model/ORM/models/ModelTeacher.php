<?php

use Nette\Security\IResource;

/**
 *
 * @author Michal Červeňák <miso@fykos.cz>
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
