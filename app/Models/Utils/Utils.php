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
        return match ($arabic) {
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
            13 => 'XIII',
            14 => 'XIV',
            15 => 'XV',
            16 => 'XVI',
            17 => 'XVII',
            18 => 'XVIII',
            19 => 'XIX',
            20 => 'XX',
            21 => 'XXI',
            22 => 'XXII',
            23 => 'XXIII',
            24 => 'XXIV',
            25 => 'XXV',
            26 => 'XXVI',
            27 => 'XXVII',
            28 => 'XXVIII',
            29 => 'XXIX',
            30 => 'XXX',
            31 => 'XXXI',
            32 => 'XXXII',
            33 => 'XXXIII',
            34 => 'XXXIV',
            35 => 'XXXV',
            36 => 'XXXVI',
            0 => '0',
            default => 'M',
        };
        // :-P
    }

    /**
     * Returns fingerprint of an object.
     * Uses __toString conversion.
     */
    public static function getFingerprint(mixed $object): string
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
            } catch (\Error $error) { // @phpstan-ignore-line
                return $error->__toString();
            }
        }
    }

    /**
     * Transform an address in order only the owner could recongize it.
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
