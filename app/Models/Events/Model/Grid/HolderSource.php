<?php

namespace FKSDB\Models\Events\Model\Grid;

use FKSDB\Models\Events\Model\Holder\Holder;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface HolderSource {
    /**
     * @return Holder[]
     */
    public function getHolders(): array;
}
