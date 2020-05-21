<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use Nette\Security\IResource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read \DateTimeInterface submitted_on
 * @property-read int submit_id
 * @property-read string source
 * @property-read string note
 * @property-read int raw_points
 * @property-read int points
 * @property-read int ct_id
 * @property-read int task_id
 * @property-read bool corrected
 */
class ModelSubmit extends AbstractModelSingle implements IResource, ITaskReferencedModel {

    const SOURCE_UPLOAD = 'upload';
    const SOURCE_POST = 'post';

    public function isEmpty(): bool {
        return !($this->submitted_on || $this->note);
    }

    public function getTask(): ModelTask {
        return ModelTask::createFromActiveRow($this->ref(DbNames::TAB_TASK, 'task_id'));
    }

    public function getContestant(): ModelContestant {
        return ModelContestant::createFromActiveRow($this->ref(DbNames::TAB_CONTESTANT_BASE, 'ct_id'));
    }

    public function getResourceId(): string {
        return 'submit';
    }

    public function getFingerprint(): string {
        return md5(implode(':', [
            $this->submit_id,
            $this->submitted_on,
            $this->source,
            $this->note,
            $this->raw_points,
        ]));
    }
}
