<?php

use Nette\Database\Table\ActiveRow as TableRow;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
abstract class AbstractModelSingle extends TableRow {

    protected $stored = true;

    public function isNew() {
        return !$this->stored;
    }

    public function setNew($value = true) {
        $this->stored = !$value;
    }

    public static function createFromTableRow(TableRow $row) {
        $model = new static($row->toArray(), $row->getTable());
        if ($model->getPrimary(false)) {
            $model->setNew(false);
        }
        return $model;
    }

}

?>
