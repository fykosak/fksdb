<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelFlag;

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServiceFlag extends AbstractServiceSingle {
    public function getModelClassName(): string {
        return ModelFlag::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_FLAG;
    }

    /**
     * Syntactic sugar.
     *
     * @param string $fid
     * @return ModelFlag|null
     */
    public function findByFid(string $fid) {
        if (!$fid) {
            return null;
        }
        /** @var ModelFlag $result */
        $result = $this->getTable()->where('fid', $fid)->fetch();
        return $result ?: null;
    }
}
