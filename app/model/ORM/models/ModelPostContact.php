<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelPostContact extends AbstractModelSingle {
    const TYPE_DELIVERY = 'D';
    const TYPE_PERMANENT = 'P';
    
    /**
     * @return ModelAddress|null
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
