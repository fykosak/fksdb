<?php

namespace FKSDB\ORM\Models;

/**
 * Interface ISchoolReferencedModel
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface ISchoolReferencedModel {
    public function getSchool(): ModelSchool;
}
