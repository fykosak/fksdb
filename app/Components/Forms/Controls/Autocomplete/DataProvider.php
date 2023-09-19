<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\Autocomplete;

use Fykosak\NetteORM\Model;

/**
 * @template TModel of Model
 * @template TData of array
 */
interface DataProvider
{
    /**
     * @phpstan-return array<int,TData> array of associative arrays with at least LABEL and VALUE keys
     */
    public function getItems(): array;

    /**
     * @phpstan-return TData
     */
    public function serializeItemId(int $id): array;

    /**
     * @phpstan-param TModel $model
     * @phpstan-return TData
     */
    public function serializeItem(Model $model): array;

    /**
     * Provider may or may not use knowledge of this update.
     *
     * @param mixed $id
     */
    public function setDefaultValue($id): void;
}
