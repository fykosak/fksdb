<?php

namespace FKSDB\ORM\Models\StoredQuery;

use FKSDB\ORM\Models\ModelSchool;

/**
 * Interface ISchoolReferencedModel
 * *
 */
interface ISchoolReferencedModel {
    public function getSchool(): ModelSchool;
}
