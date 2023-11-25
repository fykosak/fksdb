<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKSDB\Components\Forms\Controls\Autocomplete\DataProvider;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;

class PersonFactory
{
    /**
     * @phpstan-param PersonProvider $dataProvider
     * @phpstan-return AutocompleteSelectBox<PersonProvider>
     */
    public function createPersonSelect(
        bool $ajax,
        string $label,
        DataProvider $dataProvider
    ): AutocompleteSelectBox {
        /** @phpstan-var AutocompleteSelectBox<PersonProvider> $select */
        $select = new AutocompleteSelectBox($ajax, $label, 'person');
        $select->setDataProvider($dataProvider);
        return $select;
    }
}
