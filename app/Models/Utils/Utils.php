<?php

declare(strict_types=1);

namespace FKSDB\Models\Utils;

class Utils
{
    public static function ordinal(int $order): string
    {
        switch ($order) {
            case 1:
                return 'st';
            case 2:
                return 'nd';
            case 3:
                return 'rd';
            default:
                return 'th';
        }
    }

    public static function toRoman(int $arabic): string
    {
        switch ($arabic) {
            case 1:
                return 'I';
            case 2:
                return 'II';
            case 3:
                return 'III';
            case 4:
                return 'IV';
            case 5:
                return 'V';
            case 6:
                return 'VI';
            case 7:
                return 'VII';
            case 8:
                return 'VIII';
            case 9:
                return 'IX';
            case 10:
                return 'X';
            case 11:
                return 'XI';
            case 12:
                return 'XII';
            case 13:
                return 'XIII';
            case 14:
                return 'XIV';
            case 15:
                return 'XV';
            case 16:
                return 'XVI';
            case 17:
                return 'XVII';
            case 18:
                return 'XVIII';
            case 19:
                return 'XIX';
            case 20:
                return 'XX';
            case 21:
                return 'XXI';
            case 22:
                return 'XXII';
            case 23:
                return 'XXIII';
            case 24:
                return 'XXIV';
            case 25:
                return 'XXV';
            case 26:
                return 'XXVI';
            case 27:
                return 'XXVII';
            case 28:
                return 'XXVIII';
            case 29:
                return 'XXIX';
            case 30:
                return 'XXX';
            case 31:
                return 'XXXI';
            case 32:
                return 'XXXII';
            case 33:
                return 'XXXIII';
            case 34:
                return 'XXXIV';
            case 35:
                return 'XXXV';
            case 36:
                return 'XXXVI';
            case 0:
                return '0'; // Výfuk -- nultý ročník
        }
        return 'M'; // :-P
    }

    /**
     * Returns fingerprint of an object.
     * Uses __toString conversion.
     *
     * @param mixed $object
     */
    public static function getFingerprint($object): string
    {
        if (is_iterable($object)) {
            $raw = '';
            foreach ($object as $item) {
                $raw .= self::getFingerprint($item);
            }
            return md5($raw);
        } elseif ($object instanceof \DateTimeInterface) {
            return $object->format('c');
        } else {
            try {
                return (string)$object;
            } catch (\Error$error) { // @phpstan-ignore-line
                return $error->__toString();
            }
        }
    }

    /**
     * Tranform an address in order only the owner could recongize it.
     */
    public static function cryptEmail(string $email): string
    {
        [$user, $host] = explode('@', $email);
        if (strlen($user) < 3) {
            return "@$host";
        } else {
            $b = substr($user, 0, 1);
            $e = substr($user, -1);
            return "{$b}…$e@$host";
        }
    }

    /**
     * Converts string to (hopefully) valid XML element name.
     *
     * @see http://www.w3.org/TR/REC-xml/#NT-NameChar
     */
    public static function xmlName(string $string, string $prefix = '_'): string
    {
        if (preg_match('/^[0-9\.-]/', $string)) {
            $string = $prefix . $string;
        }
        return preg_replace('/ /', '-', $string);
    }
}
