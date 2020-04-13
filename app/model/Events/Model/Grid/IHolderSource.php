<?php

namespace Events\Model\Grid;

use Events\Model\Holder\Holder;
use IteratorAggregate;

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
