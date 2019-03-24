<?php

namespace FKSDB\ValidationTest\Tests;

use FKSDB\Components\Controls\PhoneNumber\PhoneNumberFactory;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ValidationTest\ValidationLog;
use FKSDB\ValidationTest\ValidationTest;

/**
 * Class PhoneNumber
 * @package FKSDB\ValidationTest
 */
class PhoneNumber extends ValidationTest {
    private static $accessKeys = ['phone', 'phone_parent_d', 'phone_parent_m'];

    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Phone number');
    }

    /**
     * @return string
     */
    public static function getAction(): string {
        return 'phone_number';
    }

    /**
     * @param ModelPerson $person
     * @return ValidationLog[]
     */
    public function run(ModelPerson $person): array {
        $log = [];
        foreach (self::$accessKeys as $key) {
            $log[] = self::validate($key, $person);
        }
        return $log;
    }


    /**
     * @param string $key
     * @param ModelPerson $person
     * @return ValidationLog
     */
    private static function validate(string $key, ModelPerson $person): ValidationLog {
        $info = $person->getInfo();
        if (!$info) {
            return new ValidationLog(self::getTitle(), 'Person info is not set', self::LVL_INFO);
        }
        $value = $info->{$key};
        if (!$value) {
            return new ValidationLog(self::getTitle(), \sprintf('"%s" is not set', $key), self::LVL_INFO);
        }
        if (!PhoneNumberFactory::isValid($value)) {
            return new ValidationLog(self::getTitle(), \sprintf('"%s" number (%s) is not valid', $key, $value), self::LVL_DANGER);
        } else {
            return new ValidationLog(self::getTitle(), \sprintf('"%s" is valid', $key), self::LVL_SUCCESS);
        }
    }


}
