<?php

use FKSDB\ORM\ModelFlag;

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServiceFlag extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_FLAG;
    protected $modelClassName = 'FKSDB\ORM\ModelFlag';

    /**
     * Syntactic sugar.
     *
     * @param integer $fid
     * @return ModelFlag|null
     */
    public function findByFid($fid) {
        if (!$fid) {
            return null;
        }
        $result = $this->getTable()->where('fid', $fid)->fetch();
        return $result ? ModelFlag::createFromTableRow($result) : null;
    }
}
