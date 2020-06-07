<?php

use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class FormUtils {

    /**
     * Convert empty strings to nulls.
     *
     * @param string|array|Traversable $values
     * @param bool $asArray
     * @return ArrayHash|array|null
     * @todo Move to general utils.
     */
    public static function emptyStrToNull($values, bool $asArray = false) {
        if ($values instanceof Traversable || is_array($values)) {
            $result = $asArray ? [] : new ArrayHash();
            foreach ($values as $key => $value) {
                $result[$key] = self::emptyStrToNull($value, $asArray);
            }
            return $result;
        } elseif ($values === '') {
            return null;
        } else {
            return $values;
        }
    }


    /**
     * @param string|array|Traversable $values
     * @return array
     * @todo Move to general utils.
     */
    public static function removeEmptyHashes(ArrayHash $values, $ignoreNulls = false) {
        $result = new ArrayHash();
        foreach ($values as $key => $value) {
            if ($value instanceof ArrayHash) {
                $clear = self::removeEmptyHashes($value, $ignoreNulls);
                if (count($clear)) {
                    $result[$key] = $clear;
                }
            } elseif (!$ignoreNulls || $value !== null) {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    public static function findFirstSubmit(Form $form) {
        foreach ($form->getComponents() as $component) {
            if ($component instanceof SubmitButton) {
                return $component;
            }
        }
        return null;
    }

}
