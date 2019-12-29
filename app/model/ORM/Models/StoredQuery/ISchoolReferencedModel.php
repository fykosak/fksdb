<?php

namespace FKSDB\ORM\Models\StoredQuery;

use FKSDB\ORM\Models\ModelSchool;

/**
 * Interface ISchoolReferencedModel
 * @package FKSDB\ORM\Models\StoredQuery
 */
interface ISchoolReferencedModel {
    /**
     * @return ModelSchool
     */
    public function getSchool(): ModelSchool;
}
