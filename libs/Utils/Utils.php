<?php

/**
 * Description of Utils
 *
 * @author michal
 */
class Utils {

    /**
     * @todo Copy+paste from old fksweb, engage more general algorithm.
     * 
     * @param int $arabic
     * @return string
     */
    public static function toRoman($arabic) {
        if (!is_numeric($arabic)) {
            $arabic = intval($arabic);
        }
        if ($arabic == 1) {
            $rim = "I";
        } elseif ($arabic == 2) {
            $rim = "II";
        } elseif ($arabic == 3) {
            $rim = "III";
        } elseif ($arabic == 4) {
            $rim = "IV";
        } elseif ($arabic == 5) {
            $rim = "V";
        } elseif ($arabic == 6) {
            $rim = "VI";
        } elseif ($arabic == 7) {
            $rim = "VII";
        } elseif ($arabic == 8) {
            $rim = "VIII";
        } elseif ($arabic == 9) {
            $rim = "IX";
        } elseif ($arabic == 10) {
            $rim = "X";
        } elseif ($arabic == 11) {
            $rim = "XI";
        } elseif ($arabic == 12) {
            $rim = "XII";
        } elseif ($arabic == 13) {
            $rim = "XIII";
        } elseif ($arabic == 14) {
            $rim = "XIV";
        } elseif ($arabic == 15) {
            $rim = "XV";
        } elseif ($arabic == 16) {
            $rim = "XVI";
        } elseif ($arabic == 17) {
            $rim = "XVII";
        } elseif ($arabic == 18) {
            $rim = "XVIII";
        } elseif ($arabic == 19) {
            $rim = "XIX";
        } elseif ($arabic == 20) {
            $rim = "XX";
        } elseif ($arabic == 21) {
            $rim = "XXI";
        } elseif ($arabic == 22) {
            $rim = "XXII";
        } elseif ($arabic == 23) {
            $rim = "XXIII";
        } elseif ($arabic == 24) {
            $rim = "XXIV";
        } elseif ($arabic == 25) {
            $rim = "XXV";
        } elseif ($arabic == 26) {
            $rim = "XXVI";
        } elseif ($arabic == 27) {
            $rim = "XXVII";
        } elseif ($arabic == 28) {
            $rim = "XXVIII";
        } elseif ($arabic == 29) {
            $rim = "XXIX";
        } elseif ($arabic == 30) {
            $rim = "XXX";
        } elseif ($arabic == 31) {
            $rim = "XXXI";
        } elseif ($arabic == 32) {
            $rim = "XXXII";
        } elseif ($arabic == 33) {
            $rim = "XXXIII";
        } elseif ($arabic == 34) {
            $rim = "XXXIV";
        } elseif ($arabic == 35) {
            $rim = "XXXV";
        } elseif ($arabic == 36) {
            $rim = "XXXVI";
        } elseif ($arabic == 0) {
            $rim = "0"; // Výfuk -- nultý ročník
        } else {
            $rim = "M"; // :-P
        }
        return $rim;
    }

}
