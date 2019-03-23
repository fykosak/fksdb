<?php

namespace FKSDB\ORM;

use Nette\Database\Table\ActiveRow;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
abstract class AbstractModelSingle extends ActiveRow implements IModel {

    protected $stored = true;

    /**
     * @return bool|mixed
     */
    public function isNew() {
        return !$this->stored;
    }

    /**
     * @param bool $value
     */
    public function setNew($value = true) {
        $this->stored = !$value;
    }

    /**
     * @param ActiveRow $row
     * @return static
     */
    public static function createFromTableRow(ActiveRow $row) {
        $model = new static($row->toArray(), $row->getTable());
        if ($model->getPrimary(false)) {
            $model->setNew(false);
        }
        return $model;
    }

}
