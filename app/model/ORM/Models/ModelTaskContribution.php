<?php

namespace FKSDB\ORM\Models;
use FKSDB\ORM\AbstractModelSingle;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property integer contribution_id
 * @property int task_id
 * @property int person_id
 */
class ModelTaskContribution extends AbstractModelSingle {

    const TYPE_AUTHOR = 'author';
    const TYPE_SOLUTION = 'solution';
    const TYPE_GRADE = 'grade';

}
