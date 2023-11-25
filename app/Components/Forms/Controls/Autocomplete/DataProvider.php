<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\Autocomplete;

/**
 * @phpstan-template TItem of array
 */
interface DataProvider
{
    /**
     * @phpstan-return TItem[] array of associative arrays with at least LABEL and VALUE keys
     */
    public function getItems(): array;

    /**
     * @phpstan-return TItem
     */
    public function getItemLabel(int $id): array;

    /**
     * Provider may or may not use knowledge of this update.
     *
     * @param mixed $id
     */
    public function setDefaultValue($id): void;
}
