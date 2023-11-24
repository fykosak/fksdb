<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\Autocomplete;

/**
 * @template TModel of \Fykosak\NetteORM\Model\Model
 * @template TData of array
 * @phpstan-extends DataProvider<TModel,TData>
 */
interface FilteredDataProvider extends DataProvider
{

    /**
     * @phpstan-return array<int,TData> see parent + filtered by the user input
     */
    public function getFilteredItems(?string $search): array;
}
