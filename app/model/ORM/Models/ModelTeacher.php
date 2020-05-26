<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\StoredQuery\ISchoolReferencedModel;
use Nette\Database\Table\ActiveRow;
use Nette\Security\IResource;

/**
 *
 * @author Michal Červeňák <miso@fykos.cz>
 * @property-read \DateTimeInterface until
 * @property-read \DateTimeInterface since
 * @property-read int school_id
 * @property-read int person_id
 * @property-read ActiveRow person
 * @property-read ActiveRow school
 * @property-read string state
 * @property-read int number_brochures
 * @property-read string note
 * @property-read int teacher_id
 */
class ModelTeacher extends AbstractModelSingle implements IResource, IPersonReferencedModel, ISchoolReferencedModel {
    const RESOURCE_ID = 'teacher';

    public function getPerson(): ModelPerson {
        return ModelPerson::createFromActiveRow($this->person);
    }

    public function getSchool(): ModelSchool {
        return ModelSchool::createFromActiveRow($this->school);
    }

    public function getResourceId(): string {
        return self::RESOURCE_ID;
    }
}
