<?php

namespace FKSDB\Components\Forms\Controls\Autocomplete;

interface FilteredDataProvider extends DataProvider {

    /**
     * @param string|null $search
     * @return array see parent + filtered by the user input
     */
    public function getFilteredItems(?string $search): array;
}
