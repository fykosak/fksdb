<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\Autocomplete;

interface DataProvider
{
    /**
     * @return array<int,array<string,scalar>> array of associative arrays with at least LABEL and VALUE keys
     */
    public function getItems(): array;

    public function getItemLabel(int $id): string;

    /**
     * Provider may or may not use knowledge of this update.
     *
     * @param mixed $id
     */
    public function setDefaultValue($id): void;
}
