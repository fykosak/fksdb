<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelSubmit extends AbstractModelSingle {

    const SOURCE_UPLOAD = 'upload';
    const SOURCE_POST = 'post';

    public static function createFromTableRow(NTableRow $row) {
        return new self($row->toArray(), $row->getTable());
    }

    /**
     * @return boolean
     */
    public function isEmpty() {
        return !($this->submitted_on || $this->note);
    }

}
