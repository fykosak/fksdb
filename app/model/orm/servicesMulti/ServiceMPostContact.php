<?php

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceMPostContact extends AbstractServiceMulti {

    protected static $staticMainServiceName = 'ServiceAddress';
    protected static $staticJoinedServiceName = 'ServicePostContact';
    protected $modelClassName = 'ModelMPostContact';

    public function __construct(ServiceAddress $mainService, ServicePostContact $joinedService) {
        parent::__construct($mainService, $joinedService);
    }

}

?>
