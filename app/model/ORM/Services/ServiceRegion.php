<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use Nette\Database\Table\Selection;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceRegion extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_REGION;
    protected $modelClassName = 'FKSDB\ORM\Models\ModelRegion';

    /**
     * @return Selection
     */
    public function getCountries(): Selection {
        return $this->getTable()->where('country_iso = nuts');
    }

}

