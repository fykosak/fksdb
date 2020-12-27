<?php

namespace FKSDB\Models\ORM\Models;

use Nette\Database\Table\ActiveRow;
use Nette\InvalidStateException;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
abstract class AbstractModelSingle extends ActiveRow {

    /**
     * @param ActiveRow $row
     * @return static
     * @throws InvalidStateException
     */
    public static function createFromActiveRow(ActiveRow $row): self {
        if ($row instanceof static) {
            return $row;
        }
        return new static($row->toArray(), $row->getTable());
    }
}