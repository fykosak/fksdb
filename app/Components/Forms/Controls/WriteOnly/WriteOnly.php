<?php

namespace FKSDB\Components\Forms\Controls\WriteOnly;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @note This interface may be later used for Containers.
 * @author Michal Koutný <michal@fykos.cz>
 */
interface WriteOnly {

    public const VALUE_ORIGINAL = '__original';

    public function setWriteOnly(bool $value = true): void;

    public function getWriteOnly(): bool;
}
