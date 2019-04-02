<?php

namespace FKSDB\ORM\Models;

use DateTime;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Database\Table\ActiveRow;
use Nette\Security\IResource;

/**
 *
 * @author Michal Červeňák <miso@fykos.cz>
 * @property-read DateTime until
 * @property-read DateTime since
 * @property-read integer school_id
 * @property-read integer person_id
 * @property-read ActiveRow person
 * @property-read ActiveRow school
 */
class ModelTeacher extends AbstractModelSingle implements IResource {
    /**
     * @return ModelPerson
     */
    public function getPerson(): ModelPerson {
        return ModelPerson::createFromTableRow($this->person);
    }

    /**
     * @return ModelSchool
     */
    public function getSchool(): ModelSchool {
        return ModelSchool::createFromTableRow($this->school);
    }

    /**
     * @return string
     */
    public function getResourceId(): string {
        return 'teacher';
    }

}
