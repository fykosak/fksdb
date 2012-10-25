<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelSubmit extends AbstractModelSingle {

    const SOURCE_UPLOAD = 'upload';
    const SOURCE_POST = 'post';

    /**
     * @return boolean
     */
    public function isEmpty() {
        return !($this->submitted_on || $this->note);
    }

}
