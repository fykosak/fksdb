<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\ORM\DbNames;

/**
 * Class PersonHistoryFactory
 * *
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PersonInfoFactory extends SingleReflectionFactory {

    protected function getTableName(): string {
        return DbNames::TAB_PERSON_INFO;
    }
}
