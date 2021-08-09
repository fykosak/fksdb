<?php

namespace FKSDB\Components\Forms\Rules;

use FKSDB\Components\Forms\Controls\WriteOnly\WriteOnly;
use Nette\Forms\Controls\BaseControl;
use Nette\OutOfRangeException;

/**
 * @author David Grudl
 * @see http://latrine.dgx.cz/jak-overit-platne-ic-a-rodne-cislo
 */
class BornNumber {

    public function __invoke(BaseControl $control): bool {
        $rc = $control->getValue();
        // suppose once validated is always valid
        if ($rc == WriteOnly::VALUE_ORIGINAL) {
            return true;
        }
        // "be liberal in what you receive"
        try {
            [$year, $month, $day, $ext, $c] = self::parseBornNumber($rc);
        } catch (OutOfRangeException $exception) {
            return false;
        }

        // do roku 1954 přidělovaná devítimístná RČ nelze ověřit
        if ($c === '') {
            return $year < 54;
        }

        // kontrolní číslice
        $mod = ($year . $month . $day . $ext) % 11;
        if ($mod === 10) {
            $mod = 0;
        }
        if ($mod !== (int)$c) {
            return false;
        }

        $originalYear = $year;
        $originalMonth = $month;
        $originalDay = $day;
        // kontrola data
        $year += $year < 54 ? 2000 : 1900;

        // k měsíci může být připočteno 20, 50 nebo 70
        if ($month > 70 && $year > 2003) {
            $month -= 70;
        } elseif ($month > 50) {
            $month -= 50;
        } elseif ($month > 20 && $year > 2003) {
            $month -= 20;
        }

        if (!checkdate($month, $day, $year)) {
            return false;
        }

        $normalized = "$originalYear$originalMonth$originalDay/$ext$c";
        $control->setValue($normalized);

        // cislo je OK
        return true;
    }

    /**
     * @param string $bornNumber
     * @return array [year,month,day,extension,control]
     * @throws OutOfRangeException
     */
    private static function parseBornNumber(string $bornNumber): array {
        if (!preg_match('#^\s*(\d\d)(\d\d)(\d\d)[ /]*(\d\d\d)(\d?)\s*$#', $bornNumber, $matches)) {
            throw new OutOfRangeException('Born number not match');
        }

        [, $year, $month, $day, $ext, $c] = $matches;
        return [$year, $month, $day, $ext, $c];
    }

    /**
     * @param string $bornNumber
     * @return string
     * @throws OutOfRangeException
     */
    public static function getGender(string $bornNumber): string {
        [, $month, , , $control] = self::parseBornNumber($bornNumber);

        // do roku 1954 přidělovaná devítimístná RČ nelze ověřit
        if ($control === '') {
            throw new OutOfRangeException('Born number before 1954');
        }
        if ($month > 50) {
            return 'F';
        } else {
            return 'M';
        }
    }
}
