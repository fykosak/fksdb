<?php

declare(strict_types=1);

namespace FKSDB\Models\Utils;

use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

class FormUtils
{
    /**
     * @phpstan-template TArray of array
     * @phpstan-param TArray $values
     * @phpstan-return TArray
     */
    public static function toPrimitive(array $values): array
    {
        $result = [];
        foreach ($values as $key => $value) {
            if (is_iterable($value)) {
                $result[$key] = self::toPrimitive((array)$value);
            } elseif ($value instanceof \DateTimeInterface) {
                $result[$key] = $value->format('c');
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
    /**
     * @phpstan-template TArray of ArrayHash
     * @phpstan-param TArray $values
     * @phpstan-return TArray
     */
    public static function emptyStrToNull(ArrayHash $values): ArrayHash
    {
        /** @phpstan-var TArray $result */
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
     * @phpstan-template TArray of array
     * @phpstan-param TArray $values
     * @phpstan-return TArray
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
        return $result;
    }

    /**
     * @phpstan-template TArray of array
     * @phpstan-param TArray $values
     * @phpstan-return TArray
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
