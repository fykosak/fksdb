<?php

namespace ORM;

use ArrayAccess;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IModel extends ArrayAccess {

    public function isNew();

    public function toArray();

    public function getPrimary($need = TRUE);

    /**
     * @note This is here to straddle duck-typing of ActiveRow.
     *
     * Returns row signature (composition of primary keys)
     * @param  bool
     * @return string
     */
    public function getSignature($need = TRUE);
}
