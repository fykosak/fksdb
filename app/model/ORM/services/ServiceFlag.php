<?php

namespace FKSDB\ORM\Services;

use AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelFlag;

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServiceFlag extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_FLAG;
    protected $modelClassName = 'FKSDB\ORM\Models\ModelFlag';

    /**
     * Syntactic sugar.
     *
     * @param integer $fid
     * @return \FKSDB\ORM\Models\ModelFlag|null
     */
    public function findByFid($fid) {
        if (!$fid) {
            return null;
        }
        $result = $this->getTable()->where('fid', $fid)->fetch();
        return $result ? ModelFlag::createFromTableRow($result) : null;
    }
}
