<?php

namespace FKSDB\ORM\Models;

use DateTime;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use Nette\Security\IResource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read DateTime submitted_on
 * @property-read integer submit_id
 * @property-read string source
 * @property-read string note
 * @property-read integer raw_points
 * @property-read int points
 * @property-read int ct_id
 * @property-read int task_id
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
    public function getTask(): ModelTask {
        $data = $this->ref(DbNames::TAB_TASK, 'task_id');
        return ModelTask::createFromTableRow($data);
    }

    /**
     * @return ModelContestant
     */
    public function getContestant(): ModelContestant {
        return ModelContestant::createFromTableRow($this->ref(DbNames::TAB_CONTESTANT_BASE, 'ct_id'));
    }

    /**
     * @return string
     */
    public function getResourceId(): string {
        return 'submit';
    }

    /**
     * @return string
     */
    public function getFingerprint() {
        return md5(implode(':', [
            $this->submit_id,
            $this->submitted_on,
            $this->source,
            $this->note,
            $this->raw_points,
        ]));
    }

}
