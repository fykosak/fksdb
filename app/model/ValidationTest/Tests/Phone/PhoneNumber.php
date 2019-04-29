<?php


namespace FKSDB\ValidationTest\Tests\Phone;

/**
 * Class PhoneNumber
 * @package FKSDB\ValidationTest\Tests\Phone
 */
class PhoneNumber extends AbstractPhoneNumber {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Phone number');
    }

    /**
     * @return string
     */
    public function getAction(): string {
        return 'phone_number';
    }

    /**
     * @return string
     */
    protected function getAccessKey(): string {
        return 'phone';
    }
}
