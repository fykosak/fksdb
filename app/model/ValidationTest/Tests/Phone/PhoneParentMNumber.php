<?php


namespace FKSDB\ValidationTest\Tests\Phone;

/**
 * Class PhoneParentMNumber
 * @package FKSDB\ValidationTest\Tests\Phone
 */
class PhoneParentMNumber extends AbstractPhoneNumber {
    /**
     * @return string
     */
    public static function getTitle(): string {
        return _('Phone parent mom number');
    }

    /**
     * @return string
     */
    public static function getAction(): string {
        return 'phone_parent_m_number';
    }

    /**
     * @return string
     */
    protected function getAccessKey(): string {
        return 'phone_parent_m';
    }
}
