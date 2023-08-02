<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKSDB\Components\Forms\Controls\Autocomplete\DataProvider;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;

class PersonFactory
{
    /**
     * @param PersonProvider $dataProvider
     * @phpstan-return AutocompleteSelectBox<PersonProvider>
     */
    public function createPersonSelect(
        bool $ajax,
        string $label,
        DataProvider $dataProvider,
        ?string $renderMethod = null
    ): AutocompleteSelectBox {
        if ($renderMethod === null) {
            $renderMethod = '$("<li>")
                        .append("<a>" + item.label + "<br>" + item.place + ", ID: " + item.value + "</a>")
                        .appendTo(ul);';
        }
        /** @phpstan-var AutocompleteSelectBox<PersonProvider> $select */
        $select = new AutocompleteSelectBox($ajax, $label, $renderMethod);
        $select->setDataProvider($dataProvider);
        return $select;
    }
}
