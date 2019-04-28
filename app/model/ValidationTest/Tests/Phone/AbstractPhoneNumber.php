<?php

namespace FKSDB\ValidationTest\Tests\Phone;

use FKSDB\Components\Controls\PhoneNumber\PhoneNumberFactory;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ValidationTest\ValidationLog;
use FKSDB\ValidationTest\ValidationTest;

/**
 * Class AbstractPhoneNumber
 * @package FKSDB\ValidationTest\Tests\Phone
 */
abstract class AbstractPhoneNumber extends ValidationTest {
    /**
     * @param ModelPerson $person
     * @return ValidationLog
     */
    public final function run(ModelPerson $person): ValidationLog {
        $info = $person->getInfo();
        if (!$info) {
            return new ValidationLog($this->getTitle(), 'Person info is not set', ValidationLog::LVL_INFO);
        }
        $value = $info->{$this->getAccessKey()};
        if (!$value) {
            return new ValidationLog($this->getTitle(), \sprintf('"%s" is not set', $this->getAccessKey()), ValidationLog::LVL_INFO);
        }
        if (!PhoneNumberFactory::isValid($value)) {
            return new ValidationLog($this->getTitle(), \sprintf('"%s" number (%s) is not valid', $this->getAccessKey(), $value), ValidationLog::LVL_DANGER);
        } else {
            return new ValidationLog($this->getTitle(), \sprintf('"%s" is valid', $this->getAccessKey()), ValidationLog::LVL_SUCCESS);
        }
    }

    /**
     * @return string
     */
    protected abstract function getAccessKey(): string;
}
