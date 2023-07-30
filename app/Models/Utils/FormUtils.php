<?php

declare(strict_types=1);

namespace FKSDB\Models\Utils;

use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;

class FormUtils
{
    // @phpstan-ignore-next-line
    public static function emptyStrToNull(iterable $values): ArrayHash
    {
        $result = new ArrayHash();
        foreach ($values as $key => $value) {
            if (is_iterable($value)) {
                $result[$key] = self::emptyStrToNull($value);
            } elseif ($value === '') {
                $result[$key] = null;
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    public static function emptyStrToNull2(iterable $values): array
    {
        $result = [];
        foreach ($values as $key => $value) {
            if (is_iterable($value)) {
                $result[$key] = self::emptyStrToNull2($value);
            } elseif ($value === '') {
                $result[$key] = null;
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    public static function removeEmptyValues(iterable $values, bool $ignoreNulls = false): array
    {
        $result = [];
        foreach ($values as $key => $value) {
            if (is_iterable($value)) {
                $clear = self::removeEmptyValues($value, $ignoreNulls);
                if (count($clear)) {
                    $result[$key] = $clear;
                }
            } elseif (!$ignoreNulls || $value !== null) {
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
