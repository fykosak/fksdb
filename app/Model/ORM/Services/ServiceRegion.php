<?php

namespace FKSDB\Model\ORM\Services;

use FKSDB\ORM\DeprecatedLazyService;
use FKSDB\Model\ORM\DbNames;
use FKSDB\Model\ORM\Models\ModelRegion;
use FKSDB\Model\ORM\Tables\TypedTableSelection;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ModelRegion findByPrimary($key)
 */
class ServiceRegion extends AbstractServiceSingle {
    use DeprecatedLazyService;

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_REGION, ModelRegion::class);
    }

    public function getCountries(): TypedTableSelection {
        return $this->getTable()->where('country_iso = nuts');
    }
}
