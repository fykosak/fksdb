<?php

namespace FKSDB\Models\ORM;

use ArrayAccess;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IModel extends ArrayAccess {
    /**
     * @return bool
     * @deprecated
     */
    public function isNew(): bool;

    public function toArray(): array;

    /**
     * @param bool $throw
     * @return string|int
     */
    public function getPrimary(bool $throw = true);

    /**
     * @note This is here to straddle duck-typing of ActiveRow.
     *
     * Returns row signature (composition of primary keys)
     * @param bool
     * @return string
     */
    public function getSignature(bool $need = true): string;
}
