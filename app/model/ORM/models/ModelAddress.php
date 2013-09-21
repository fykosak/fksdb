<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelAddress extends AbstractModelSingle {

    const UNKNOWN_REGION = 1;

    /**
     * Try to infer region from postal code.
     * Returns result of the inference (if unsuccessful sets the unknown region.
     * 
     * @todo Refactor dependencies.
     * @return boolean
     */
    public function inferRegion() {
        if (isset($this->region_id)) {
            return true;
        }
        if (!isset($this->postal_code)) {
            $this->region_id = self::UNKNOWN_REGION;
            return false;
        }

        $row = $this->getTable()->getConnection()->table('psc_region')->where('psc = ?', $this->postal_code)->fetch();
        if ($row) {
            $this->region_id = $row->region_id;
            return true;
        } else {
            $this->region_id = self::UNKNOWN_REGION;
            return false;
        }
    }

}
