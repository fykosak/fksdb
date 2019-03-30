<?php

use Nette\Utils\ArrayHash;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class FormUtils {

    /**
     * Convert empty strings to nulls.
     *
     * @todo Move to general utils.
     * @param string|array|Traversable $values
     * @return array
     */
    public static function emptyStrToNull($values) {
        if ($values instanceof Traversable || is_array($values)) {
            $result = new ArrayHash();
            foreach ($values as $key => $value) {
                $result[$key] = self::emptyStrToNull($value);
            }
            return $result;
        } else if ($values === '') {
            return null;
        } else {
            return $values;
        }
    }

    /**
     * @todo Move to general utils.
     * @param string|array|Traversable $values
     * @return array
     */
    public static function removeEmptyHashes(ArrayHash $values, $ignoreNulls = false) {
        $result = new ArrayHash();
        foreach ($values as $key => $value) {
            if ($value instanceof ArrayHash) {
                $clear = self::removeEmptyHashes($value, $ignoreNulls);
                if (count($clear)) {
                    $result[$key] = $clear;
                }
            } else if (!$ignoreNulls || $value !== null) {
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
