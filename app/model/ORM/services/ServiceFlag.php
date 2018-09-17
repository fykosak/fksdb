<?php

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServiceFlag extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_FLAG;
    protected $modelClassName = 'ModelFlag';

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
        return $result ? : null;
    }
}
