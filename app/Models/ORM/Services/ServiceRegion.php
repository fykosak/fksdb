<?php

namespace FKSDB\Models\ORM\Services;



use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelRegion;
use FKSDB\Models\ORM\Tables\TypedTableSelection;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ModelRegion findByPrimary($key)
 */
class ServiceRegion extends AbstractServiceSingle {


    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_REGION, ModelRegion::class);
    }

    public function getCountries(): TypedTableSelection {
        return $this->getTable()->where('country_iso = nuts');
    }
}
