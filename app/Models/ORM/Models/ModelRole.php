<?php

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\AbstractModel;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelRole extends AbstractModel {

    public const CONTESTANT = 'contestant';
    public const ORG = 'org';
    public const REGISTERED = 'registered';
    public const GUEST = 'guest';
}
