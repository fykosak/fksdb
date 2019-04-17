<?php

namespace FKSDB\ORM\Models;

use DateTime;
use FKSDB\ORM\AbstractModelSingle;
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
class ModelTeacher extends AbstractModelSingle implements IResource, IPersonReferencedModel {
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
