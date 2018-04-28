<?php

use Nette\Security\IResource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property DateTime submitted_on
 * @property string note
 * @property integer submit_id
 * @property string source
 * @property integer raw_points
 * @property integer ct_id
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
        $data = $this->ref(DbNames::TAB_TASK, 'task_id');
        return ModelTask::createFromTableRow($data);
    }

    /**
     * @return ModelContestant
     */
    public function getContestant() {
        return ModelContestant::createFromTableRow($this->ref(DbNames::TAB_CONTESTANT_BASE, 'ct_id'));
    }

    public function getResourceId() {
        return 'submit';
    }

    public function getFingerprint() {
        return md5(implode(':', array(
            $this->submit_id,
            $this->submitted_on,
            $this->source,
            $this->note,
            $this->raw_points,
        )));
    }

}
