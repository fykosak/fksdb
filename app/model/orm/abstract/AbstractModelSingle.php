<?php

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
abstract class AbstractModelSingle extends NTableRow {

    protected $stored = true;

    public function isNew() {
        return !$this->stored;
    }

    public function setNew($value = true) {
        $this->stored = !$value;
    }

}

?>
