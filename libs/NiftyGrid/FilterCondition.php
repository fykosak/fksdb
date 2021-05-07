<?php
/**
 * NiftyGrid - DataGrid for Nette
 *
 * @author    Jakub Holub
 * @copyright    Copyright (c) 2012 Jakub Holub
 * @license     New BSD Licence
 * @link        http://addons.nette.org/cs/niftygrid
 */

namespace NiftyGrid;

use Nette\SmartObject;
use Nette\Utils\Strings;

/**
 * @author     Jakub Holub
 */
class FilterCondition {

    use SmartObject;

    /* filter types */
    public const TEXT = 'text';
    public const SELECT = 'select';
    public const NUMERIC = 'numeric';
    public const DATE = 'date';
    public const BOOLEAN = 'boolean';

    /* condition types */
    public const WHERE = ' WHERE ';

    /* conditions names */
    public const CONTAINS = 'contains';
    public const STARTSWITH = 'startsWith';
    public const ENDSWITH = 'endsWith';
    public const EQUAL = 'equal';
    public const HIGHER = 'higher';
    public const HIGHEREQUAL = 'higherEqual';
    public const LOWER = 'lower';
    public const LOWEREQUAL = 'lowerEqual';
    public const DIFFERENT = 'different';

    public const DATE_EQUAL = 'dateEqual';
    public const DATE_HIGHER = 'dateHigher';
    public const DATE_HIGHEREQUAL = 'dateHigherEqual';
    public const DATE_LOWER = 'dateLower';
    public const DATE_LOWEREQUAL = 'dateLowerEqual';
    public const DATE_DIFFERENT = 'dateDifferent';

    /**
     * @static
     * @param string $s
     * @return mixed
     */
    public static function like(string $s): string {
        $escape = ['.', '%', '_', '\''];
        $replace = ['\.', '\%', '\_'];
        return str_replace($escape, $replace, $s);
    }

    public static function getConditionsByType(string $type): array {
        switch ($type) {
            case self::TEXT:
                return [
                    self::ENDSWITH => '%',
                    self::STARTSWITH => '%',
                ];
            case self::DATE:
                return [
                    self::DATE_EQUAL => '=',
                    self::DATE_DIFFERENT => '<>',
                    self::DATE_HIGHEREQUAL => '>=',
                    self::DATE_HIGHER => '>',
                    self::DATE_LOWEREQUAL => '<=',
                    self::DATE_LOWER => '<',
                ];
            case self::NUMERIC:
                return [
                    self::EQUAL => '=',
                    self::DIFFERENT => '<>',
                    self::HIGHEREQUAL => '>=',
                    self::HIGHER => '>',
                    self::LOWEREQUAL => '<=',
                    self::LOWER => '<',
                ];
            default:
                return [];
        }
    }

    /**
     * @static
     * @param string $value
     * @param string $type
     * @return array
     */
    public static function prepareFilter(string $value, string $type): array {
        /* select nebo boolean muze byt pouze equal */
        if ($type == self::SELECT || $type == self::BOOLEAN)
            return [
                'condition' => self::EQUAL,
                'value' => $value,
            ];
        elseif ($type == self::TEXT) {
            foreach (self::getConditionsByType(self::TEXT) as $name => $condition) {
                if (Strings::endsWith($value, $condition) && !Strings::startsWith($value, $condition) && $name == self::STARTSWITH)
                    return [
                        'condition' => $name,
                        'value' => Strings::substring($value, 0, '-' . Strings::length($condition)),
                    ];
                elseif (Strings::startsWith($value, $condition) && !Strings::endsWith($value, $condition) && $name == self::ENDSWITH)
                    return [
                        'condition' => $name,
                        'value' => Strings::substring($value, Strings::length($condition)),
                    ];
            }
            return [
                'condition' => self::CONTAINS,
                'value' => $value,
            ];
        } elseif ($type == self::DATE) {
            foreach (self::getConditionsByType(self::DATE) as $name => $condition) {
                if (Strings::startsWith($value, $condition))
                    return [
                        'condition' => $name,
                        'value' => Strings::substring($value, Strings::length($condition)),
                    ];
            }
            return [
                'condition' => self::DATE_EQUAL,
                'value' => $value,
            ];
        } elseif ($type == self::NUMERIC) {
            foreach (self::getConditionsByType(self::NUMERIC) as $name => $condition) {
                if (Strings::startsWith($value, $condition))
                    return [
                        'condition' => $name,
                        'value' => (int)Strings::substring($value, Strings::length($condition)),
                    ];
            }
            return [
                'condition' => self::EQUAL,
                'value' => (int)$value,
            ];
        }
    }

    public static function contains(string $value): array {
        return [
            'type' => self::WHERE,
            'datatype' => self::TEXT,
            'cond' => ' LIKE ?',
            'value' => '%' . self::like($value) . '%',
        ];
    }

    public static function equal(string $value): array {
        return [
            'type' => self::WHERE,
            'datatype' => self::TEXT,
            'cond' => ' = ?',
            'value' => $value,
        ];
    }

    public static function startsWith(string $value): array {
        return [
            'type' => self::WHERE,
            'datatype' => self::TEXT,
            'cond' => ' LIKE ?',
            'value' => self::like($value) . '%',
        ];
    }

    public static function endsWith(string $value): array {
        return [
            'type' => self::WHERE,
            'datatype' => self::TEXT,
            'cond' => ' LIKE ?',
            'value' => '%' . self::like($value),
        ];
    }

    public static function higher(string $value): array {
        return [
            'type' => self::WHERE,
            'datatype' => self::NUMERIC,
            'cond' => ' > ?',
            'value' => $value,
        ];
    }

    public static function higherEqual(string $value): array {
        return [
            'type' => self::WHERE,
            'datatype' => self::NUMERIC,
            'cond' => ' >= ?',
            'value' => $value,
        ];
    }

    public static function lower(string $value): array {
        return [
            'type' => self::WHERE,
            'datatype' => self::NUMERIC,
            'cond' => ' < ?',
            'value' => $value,
        ];
    }

    public static function lowerEqual(string $value): array {
        return [
            'type' => self::WHERE,
            'datatype' => self::NUMERIC,
            'cond' => ' <= ?',
            'value' => $value,
        ];
    }

    public static function different(string $value): array {
        return [
            'type' => self::WHERE,
            'datatype' => self::NUMERIC,
            'cond' => ' <> ?',
            'value' => $value,
        ];
    }

    public static function dateEqual(string $value): array {
        return [
            'type' => self::WHERE,
            'datatype' => self::DATE,
            'cond' => ' = ',
            'value' => $value,
            'columnFunction' => 'DATE',
            'valueFunction' => 'DATE',
        ];
    }

    public static function dateHigher(string $value): array {
        return [
            'type' => self::WHERE,
            'datatype' => self::DATE,
            'cond' => ' > ',
            'value' => $value,
            'columnFunction' => 'DATE',
            'valueFunction' => 'DATE',
        ];
    }

    public static function dateHigherEqual(string $value): array {
        return [
            'type' => self::WHERE,
            'datatype' => self::DATE,
            'cond' => ' >= ',
            'value' => $value,
            'columnFunction' => 'DATE',
            'valueFunction' => 'DATE',
        ];
    }

    public static function dateLower(string $value): array {
        return [
            'type' => self::WHERE,
            'datatype' => self::DATE,
            'cond' => ' < ',
            'value' => $value,
            'columnFunction' => 'DATE',
            'valueFunction' => 'DATE',
        ];
    }

    public static function dateLowerEqual(string $value): array {
        return [
            'type' => self::WHERE,
            'datatype' => self::DATE,
            'cond' => ' <= ',
            'value' => $value,
            'columnFunction' => 'DATE',
            'valueFunction' => 'DATE',
        ];
    }

    public static function dateDifferent(string $value): array {
        return [
            'type' => self::WHERE,
            'datatype' => self::DATE,
            'cond' => ' <> ',
            'value' => $value,
            'columnFunction' => 'DATE',
            'valueFunction' => 'DATE',
        ];
    }
}
