<?php

namespace FKSDB\ValidationTest\Tests;

use FKSDB\Components\Forms\Rules\BornNumber;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ValidationTest\ValidationLog;
use FKSDB\ValidationTest\ValidationTest;

/**
 * Class GenderFromBornNumber
 * @package FKSDB\ValidationTest\Tests
 */
class GenderFromBornNumber extends ValidationTest {
    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Gender from born number');
    }

    /**
     * @return string
     */
    public static function getAction(): string {
        return 'gender_from_born_number';
    }

    /**
     * @param ModelPerson $person
     * @return ValidationLog
     */
    public function run(ModelPerson $person): ValidationLog {
        $info = $person->getInfo();

        if (!$info) {
            return new ValidationLog(self::getTitle(), 'Person info is not set', self::LVL_INFO);
        }

        if (!$person->gender) {
            return new ValidationLog(self::getTitle(), _('Gender is not set'), self::LVL_WARNING);
        }
        if (!$info->born_id) {
            return new ValidationLog(self::getTitle(), _('Born number is not set'), self::LVL_INFO);
        }

        if (BornNumber::getGender($info->born_id) != $person->gender) {
            return new ValidationLog(self::getTitle(), 'Gender not match born number', self::LVL_DANGER);
        } else {
            return new ValidationLog(self::getTitle(), 'Gender match born number', self::LVL_SUCCESS);
        }
    }

}
