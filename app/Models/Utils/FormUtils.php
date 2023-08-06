<?php

declare(strict_types=1);

namespace FKSDB\Models\Utils;

use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;

class FormUtils
{
    /**
     * @template TValue
     * @phpstan-param ArrayHash<TValue> $values
     * @phpstan-return ArrayHash<TValue>
     */
    public static function emptyStrToNull(ArrayHash $values): ArrayHash
    {
        $result = new ArrayHash();
        foreach ($values as $key => $value) {
            if ($value instanceof ArrayHash) {
                $result[$key] = self::emptyStrToNull($value);
            } elseif ($value === '') {
                $result[$key] = null;
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * @template TValue
     * @phpstan-param array<TValue> $values
     * @phpstan-return array<TValue|null>
     */
    public static function emptyStrToNull2(array $values): array
    {
        $result = [];
        foreach ($values as $key => $value) {
            if (is_iterable($value)) {
                $result[$key] = self::emptyStrToNull2((array)$value);
            } elseif ($value === '') {
                $result[$key] = null;
            } else {
                $result[$key] = $value;
            }
        }
        return $result; //@phpstan-ignore-line
    }

    /**
     * @template TValue
     * @phpstan-param array<TValue> $values
     * @phpstan-return array<TValue>
     */
    public static function removeEmptyValues(array $values, bool $ignoreNulls = false): array
    {
        $result = [];
        foreach ($values as $key => $value) {
            if (is_iterable($value)) {
                $clear = self::removeEmptyValues((array)$value, $ignoreNulls);
                if (count($clear)) {
                    $result[$key] = $clear;
                }
            } elseif (!$ignoreNulls || $value !== null) {
                $result[$key] = $value;
            }
        }
        return $result; //@phpstan-ignore-line
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
