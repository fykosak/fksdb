<?php

namespace FKSDB\ORM\Models;

use DateTime;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use Nette\Security\IResource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-readDateTime submitted_on
 * @property-readinteger submit_id
 * @property-readstring source
 * @property-readstring note
 * @property-readinteger raw_points
 * @property-readint points
 * @property-readint ct_id
 * @property-readint task_id
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
