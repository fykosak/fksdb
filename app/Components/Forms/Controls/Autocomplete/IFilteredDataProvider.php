<?php

namespace FKSDB\Components\Forms\Controls\Autocomplete;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IFilteredDataProvider extends IDataProvider {

    /**
     * @param string|null $search
     * @return array see parent + filtered by the user input
     */
    public function getFilteredItems(?string $search): array;
}
