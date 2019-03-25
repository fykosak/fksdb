<?php

namespace FKSDB\ValidationTest;

use FKSDB\ORM\Models\ModelPerson;

/**
 * Class ValidationTest
 */
abstract class ValidationTest {
    const LVL_DANGER = 'danger';
    const LVL_SUCCESS = 'success';
    const LVL_WARNING = 'warning';
    const LVL_INFO = 'info';

    /**
     * @return array
     */
    public static function getAvailableLevels(): array {
        return [self::LVL_DANGER, self::LVL_WARNING, self::LVL_SUCCESS, self::LVL_INFO];
    }

    /**
     * @param ModelPerson $person
     * @return ValidationLog
     */
    abstract public function run(ModelPerson $person): ValidationLog;

    /**
     * @return string
     */
    abstract public static function getTitle(): string;

    /**
     * @return string
     */
    abstract public static function getAction(): string;
}
