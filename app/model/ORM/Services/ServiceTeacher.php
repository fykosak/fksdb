<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\DeprecatedLazyDBTrait;
use FKSDB\ORM\Models\ModelTeacher;

/**
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServiceTeacher extends AbstractServiceSingle {
    use DeprecatedLazyDBTrait;

    public function getModelClassName(): string {
        return ModelTeacher::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_TEACHER;
    }
}
