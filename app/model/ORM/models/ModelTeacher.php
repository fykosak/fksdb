<?php

namespace FKSDB\ORM;

use AbstractModelSingle;
use DateTime;
use Nette\Database\Table\ActiveRow;
use Nette\Security\IResource;

/**
 *
 * @author Michal Červeňák <miso@fykos.cz>
 * @property DateTime until
 * @property DateTime since
 * @property integer school_id
 * @property integer person_id
 * @property ActiveRow person
 * @property ActiveRow school
 */
class ModelTeacher extends AbstractModelSingle implements IResource {

    public function getPerson(): ModelPerson {
        $data = $this->person;
        return ModelPerson::createFromTableRow($data);
    }

    public function getSchool(): ModelSchool {
        $data = $this->school;
        return ModelSchool::createFromTableRow($data);
    }

    public function getResourceId(): string {
        return 'teacher';
    }

}
