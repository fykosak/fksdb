<?php

use Nette\ArrayHash;
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
     * @todo Mode to general utils.
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
     * Convert empty strings to nulls.
     * 
     * @todo Mode to general utils.
     * @param string|array|Traversable $values
     * @return array
     */
    public static function removeEmptyHashes(ArrayHash $values) {
        $result = new ArrayHash();
        foreach ($values as $key => $value) {
            if ($value instanceof ArrayHash) {
                $clear = self::removeEmptyHashes($value);
                if (count($clear)) {
                    $result[$key] = $clear;
                }
            } else {
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
