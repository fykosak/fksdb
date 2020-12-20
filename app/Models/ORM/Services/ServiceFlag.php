<?php

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\DeprecatedLazyDBTrait;
use FKSDB\Models\ORM\Models\ModelFlag;

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServiceFlag extends AbstractServiceSingle {

    use DeprecatedLazyDBTrait;

    /**
     * Syntactic sugar.
     *
     * @param string $fid
     * @return ModelFlag|null
     */
    public function findByFid($fid): ?ModelFlag {
        if (!$fid) {
            return null;
        }
        /** @var ModelFlag $result */
        $result = $this->getTable()->where('fid', $fid)->fetch();
        return $result ?: null;
    }
}
