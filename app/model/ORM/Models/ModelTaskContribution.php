<?php

namespace FKSDB\ORM\Models;
use FKSDB\ORM\AbstractModelSingle;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-readinteger contribution_id
 * @property-readint task_id
 * @property-readint person_id
 */
class ModelTaskContribution extends AbstractModelSingle {

    const TYPE_AUTHOR = 'author';
    const TYPE_SOLUTION = 'solution';
    const TYPE_GRADE = 'grade';

}
