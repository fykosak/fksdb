<?php

declare(strict_types=1);

namespace FKSDB\Models\Utils;

use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;

class FormUtils
{

    /**
     * Convert empty strings to nulls.
     *
     * @param string|iterable $values
     * @param bool $asArray
     * @return iterable|null
     * @todo Move to general utils.
     */
    public static function emptyStrToNull($values, bool $asArray = false)
    {
        if (is_iterable($values)) {
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
     * @param ArrayHash $values
     * @param bool $ignoreNulls
     * @return ArrayHash
     * @todo Move to general utils.
     */
    public static function removeEmptyHashes(ArrayHash $values, bool $ignoreNulls = false): ArrayHash
    {
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

    public static function removeEmptyValues(array $values): array
    {
        $result = [];
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $clear = self::removeEmptyValues($value);
                if (count($clear)) {
                    $result[$key] = $clear;
                }
            } elseif ($value !== null) {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    public static function findFirstSubmit(Form $form): ?SubmitButton
    {
        foreach ($form->getComponents() as $component) {
            if ($component instanceof SubmitButton) {
                return $component;
            }
        }
        return null;
    }
}
