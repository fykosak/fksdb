<?php

namespace FKSDB\Components\DatabaseReflection\Person;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\Tables\Traits\PersonLinkTrait;

/**
 * Class PersonLinkRowFactory
 * @package FKSDB\Components\DatabaseReflection\Person
 */
class PersonLinkRowFactory extends AbstractRow {
    use PersonLinkTrait;

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

}
