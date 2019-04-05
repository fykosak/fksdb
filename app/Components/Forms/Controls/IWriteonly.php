<?php

namespace FKSDB\Components\Forms\Containers;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @note This interface may be later used for Containers.
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IWriteOnly {

    const VALUE_ORIGINAL = '__original';

    /**
     * @param bool $value
     * @return mixed
     */
    public function setWriteOnly($value = true);

    public function getWriteOnly();
}
