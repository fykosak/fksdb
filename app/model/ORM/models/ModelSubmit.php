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

    /**
     * @return ModelTask
     */
    public function getTask() {
        return ModelTask::createFromTableRow($this->ref(DbNames::TAB_TASK, 'task_id'));
    }

    /**
     * @return ModelContestant
     */
    public function getContestant() {
        return ModelContestant::createFromTableRow($this->ref(DbNames::TAB_CONTESTANT, 'ct_id'));
    }

}
