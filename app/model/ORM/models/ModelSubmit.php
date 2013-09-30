<?php

use Nette\Security\IResource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelSubmit extends AbstractModelSingle implements IResource {

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

    public function getResourceId() {
        return 'submit';
    }

    public function getFingerprint() {
        return md5(implode(':', array(
            $this->submitted_on,
            $this->source,
            $this->note,
            $this->raw_points,
        )));
    }

    /*
     * Detection of task change.
     * (Consider implementic this mechanism in general into AbstractModelSingle.)
     */

    private $originalTaskId = null;

    public function getOriginalTaskId() {
        return $this->originalTaskId;
    }

    public function __set($key, $value) {
        if ($key == 'task_id') {
            if ($value != $this->task_id) {
                $this->originalTaskId = $this->task_id;
            } else {
                $this->originalTaskId = $value;
            }
        }
        parent::__set($key, $value);
    }

}
