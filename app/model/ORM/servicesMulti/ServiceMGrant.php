<?php

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceMGrant extends AbstractServiceMulti {

    protected $modelClassName = 'ModelMGrant';
    protected $joiningColumn = 'role_id';

    /**
     * ServiceMGrant constructor.
     * @param ServiceRole $mainService
     * @param ServiceGrant $joinedService
     */
    public function __construct(ServiceRole $mainService, ServiceGrant $joinedService) {
        parent::__construct($mainService, $joinedService);
    }

}


