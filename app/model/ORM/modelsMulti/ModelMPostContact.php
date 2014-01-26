<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelMPostContact extends AbstractModelMulti {

    /**
     * @return ModelAddress
     */
    public function getAddress() {
        return $this->getMainModel();
    }

    public function getPostContact() {
        return $this->getJoinedModel();
    }

}
