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
        return ModelPerson::createFromTableRow($this->person);
    }

    public function getSchool(): ModelSchool {
        return ModelSchool::createFromTableRow( $this->school);
    }

    public function getResourceId(): string {
        return 'teacher';
    }

}
