<?php

namespace FKSDB\Model\Events\Model\Grid;

use FKSDB\Model\Events\Model\Holder\Holder;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IHolderSource {
    /**
     * @return Holder[]
     */
    public function getHolders(): array;
}
