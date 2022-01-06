<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\WriteOnly;

/**
 * @note This interface may be later used for Containers.
 */
interface WriteOnly {

    public const VALUE_ORIGINAL = '__original';

    public function setWriteOnly(bool $value = true): void;

    public function getWriteOnly(): bool;
}
