<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\Autocomplete;

interface FilteredDataProvider extends DataProvider
{

    /**
     * @phpstan-return array<int,array<string,scalar>> see parent + filtered by the user input
     */
    public function getFilteredItems(?string $search): array;
}
