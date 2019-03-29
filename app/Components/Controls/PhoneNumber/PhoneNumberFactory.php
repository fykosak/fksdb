<?php

namespace FKSDB\Components\Controls\PhoneNumber;

use Closure;
use FKSDB\Components\Controls\PhoneNumber\Region\AbstractRegion;
use FKSDB\Components\Controls\PhoneNumber\Region\Czech;
use FKSDB\Components\Controls\PhoneNumber\Region\Slovakia;
use FKSDB\Components\Controls\PhoneNumber\Region\Spain;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class PhoneNumberFactory
 * @package FKSDB\Components\Controls
 */
class PhoneNumberFactory {
    /**
     * @var AbstractRegion[]
     */
    static $regions = [Slovakia::class, Czech::class, Spain::class];

    /**
     * @param $number
     * @return Html
     */
    public static function format(string $number): Html {
        try {
            foreach (static::$regions as $region) {
                if ($region::match($number)) {
                    return $region::create($number);
                }
            }
        } catch (InvalidPhoneNumberException $exception) {
        }
        return Html::el('span')->addAttributes(['class' => 'badge badge-danger'])->addText($number);
    }

    /**
     * @param string $number
     * @return bool
     */
    public static function isValid(string $number): bool {
        foreach (static::$regions as $region) {
            if ($region::match($number)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return Closure
     */
    public static function getFormValidationCallback(): Closure {
        return function (BaseControl $control): bool {
            $value = $control->getValue();
            foreach (static::$regions as $region) {
                if ($region::match($value)) {
                    return true;
                }
            }
            return false;
        };

    }
}
