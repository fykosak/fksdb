<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use Nette\Database\Table\Selection;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceSchool extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_SCHOOL;
    protected $modelClassName = 'FKSDB\ORM\Models\ModelSchool';

    /**
     * @return \Nette\Database\Table\Selection
     */
    public function getSchools(): Selection {
        return $this->getTable()
            ->select(DbNames::TAB_SCHOOL . '.*')
            ->select(DbNames::TAB_ADDRESS . '.*');
    }

}

