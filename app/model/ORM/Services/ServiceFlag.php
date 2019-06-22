<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelFlag;

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServiceFlag extends AbstractServiceSingle {
    /**
     * @return string
     */
    public function getModelClassName(): string {
        return ModelFlag::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_FLAG;
    }

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
        return $result ? ModelFlag::createFromActiveRow($result) : null;
    }
}
