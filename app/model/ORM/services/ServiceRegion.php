<?php

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceRegion extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_REGION;
    protected $modelClassName = 'FKSDB\ORM\ModelRegion';

    /**
     * @return TypedTableSelection
     */
    public function getCountries() {
        return $this->getTable()->where('country_iso = nuts');
    }

}

