<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class FormUtils {

    /**
     * Convert empty strings to nulls.
     * 
     * @param array|Traversable $values
     * @return array
     */
    public static function emptyStrToNull($values) {
        $result = array();
        foreach ($values as $key => $value) {
            $result[$key] = $value === '' ? null : $value;
        }
        return $result;
    }

}
