<?php

use ORM\IModel;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceMPostContact extends AbstractServiceMulti {

    protected $modelClassName = 'ModelMPostContact';
    protected $joiningColumn = 'address_id';

    /**
     * ServiceMPostContact constructor.
     * @param ServiceAddress $mainService
     * @param ServicePostContact $joinedService
     */
    public function __construct(ServiceAddress $mainService, ServicePostContact $joinedService) {
        parent::__construct($mainService, $joinedService);
    }

    /**
     * Delete post contact including the address.
     * @param IModel $model
     */
    public function dispose(IModel $model) {
        parent::dispose($model);
        $this->getMainService()->dispose($model->getMainModel());
    }

}


