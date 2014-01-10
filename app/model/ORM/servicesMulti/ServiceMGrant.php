<?php

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceMGrant extends AbstractServiceMulti {

    protected $modelClassName = 'ModelMGrant';

    public function __construct(ServiceRole $mainService, ServiceGrant $joinedService) {
        parent::__construct($mainService, $joinedService);
    }

}

?>
