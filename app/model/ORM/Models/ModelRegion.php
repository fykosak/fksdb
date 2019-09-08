<?php

namespace FKSDB\ORM\Models;

use FKSDB\Components\Controls\PhoneNumber\InvalidPhoneNumberException;
use FKSDB\ORM\AbstractModelSingle;
use Tracy\Debugger;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read integer region_id
 * @property-read string country_iso
 * @property-read string nuts
 * @property-read string name
 * @property-read int phone_nsn
 * @property-read string phone_prefix
 */
class ModelRegion extends AbstractModelSingle {

    const CZECH_REPUBLIC = 3;
    const SLOVAKIA = 2;

    /**
     * @param string $number
     * @return bool
     */
    public function matchPhone(string $number): bool {
        if (\is_null($this->phone_nsn) || \is_null($this->phone_prefix)) {
            return false;
        }
        return !!\preg_match('/^\\' . $this->phone_prefix . '\d{' . $this->phone_nsn . '}/', $number);
    }

    /**
     * @param string $number
     * @return string
     * @throws InvalidPhoneNumberException
     */
    public function formatPhoneNumber(string $number): string {
        $regExp = null;
        switch ($this->phone_nsn) {
            case 9:
                $regExp = '(\d{3})(\d{3})(\d{3})';
                break;
            default:
                $regExp = '(\d{' . $this->phone_nsn . '})';
        }
        Debugger::barDump('/^' . $this->phone_prefix . $regExp . '$/');
        if (preg_match('/^\\' . $this->phone_prefix . $regExp . '$/', $number, $matches)) {
            unset($matches[0]);
            return $this->phone_prefix . ' ' . \implode(' ', $matches);
        }
        throw new InvalidPhoneNumberException('number not match');
    }
}
