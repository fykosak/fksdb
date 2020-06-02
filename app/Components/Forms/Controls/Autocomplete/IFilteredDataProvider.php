<?php

namespace FKSDB\Components\Forms\Controls\Autocomplete;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IFilteredDataProvider extends IDataProvider {

    public function getFilteredItems(string $search): array;
}
