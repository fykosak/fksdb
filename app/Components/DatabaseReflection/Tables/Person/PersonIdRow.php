<?php

namespace FKSDB\Components\DatabaseReflection\Person;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class PersonIdRow
 * @package FKSDB\Components\DatabaseReflection\Person
 */
class PersonIdRow extends AbstractRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Person Id');
    }

    /**
     * @return int
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_ALLOW_BASIC;
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'person_id';
    }
}
