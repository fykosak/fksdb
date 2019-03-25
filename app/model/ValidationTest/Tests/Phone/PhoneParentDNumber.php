<?php


namespace FKSDB\ValidationTest\Tests\Phone;

/**
 * Class PhoneParentDNumber
 * @package FKSDB\ValidationTest\Tests\Phone
 */
class PhoneParentDNumber extends AbstractPhoneNumber {
    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Phone parent dad number');
    }

    /**
     * @return string
     */
    public static function getAction(): string {
        return 'phone_parent_d_number';
    }

    /**
     * @return string
     */
    protected function getAccessKey(): string {
        return 'phone_parent_d';
    }
}
