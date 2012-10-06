<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelPostContact extends AbstractModelSingle {

    /**
     * 
     * @return \ModelAddress|null
     */
    public function getAddress() {
        $address = $this->address;
        if ($address) {
            return new ModelAddress($address->toArray(), $address->getTable());
        } else {
            return null;
        }
    }

}
