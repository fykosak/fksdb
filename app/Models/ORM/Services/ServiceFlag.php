<?php

namespace FKSDB\Models\ORM\Services;


use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\DeprecatedLazyDBTrait;
use FKSDB\Models\ORM\Models\ModelFlag;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServiceFlag extends AbstractServiceSingle {
    use DeprecatedLazyDBTrait;

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_FLAG, ModelFlag::class);
    }

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
