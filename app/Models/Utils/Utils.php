<?php

declare(strict_types=1);

namespace FKSDB\Models\Utils;

class Utils
{
    public static function ordinal(int $order): string
    {
        $ends = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
        if (($order % 100) >= 11 && ($order % 100) <= 13) {
            return 'th';
        } else {
            return $ends[$order % 10];
        }
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
            } catch (\Error $error) { // @phpstan-ignore-line
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
