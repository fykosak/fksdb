<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\Autocomplete;

/**
 * @phpstan-template TItem of array
 * @phpstan-extends DataProvider<TItem>
 */
interface FilteredDataProvider extends DataProvider
{
    /**
     * @phpstan-return array<int,TItem> see parent + filtered by the user input
     */
    public function getFilteredItems(?string $search): array;
}
