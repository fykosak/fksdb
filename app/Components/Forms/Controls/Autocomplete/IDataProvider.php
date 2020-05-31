<?php

namespace FKSDB\Components\Forms\Controls\Autocomplete;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IDataProvider {

    public const LABEL = 'label';
    public const VALUE = 'value';

    /**
     * @return array array of associative arrays with at least LABEL and VALUE keys
     */
    public function getItems(): array;

    /**
     * @param mixed $id
     * @return string
     */
    public function getItemLabel($id): string;

    /**
     * Provider may or may not use knowledge of this update.
     *
     * @param mixed id
     */
    public function setDefaultValue($id);
}
