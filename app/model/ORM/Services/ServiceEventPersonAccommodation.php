<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelEventPersonAccommodation;

/**
 * Class FKSDB\ORM\Services\ServiceEventPersonAccommodation
 * @deprecated
 */
class ServiceEventPersonAccommodation extends AbstractServiceSingle {
    /**
     * @return string
     */
    protected function getModelClassName(): string {
        return ModelEventPersonAccommodation::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_EVENT_PERSON_ACCOMMODATION;
    }
}
