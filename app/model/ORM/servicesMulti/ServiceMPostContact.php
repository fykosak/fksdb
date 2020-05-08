<?php

use FKSDB\ORM\AbstractModelMulti;
use FKSDB\ORM\AbstractServiceMulti;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Services\ServiceAddress;
use FKSDB\ORM\Services\ServicePostContact;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceMPostContact extends AbstractServiceMulti {

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
     * @param IModel|AbstractModelMulti $model
     */
    public function dispose(IModel $model) {
        parent::dispose($model);
        $this->getMainService()->dispose($model->getMainModel());
    }

    public function getJoiningColumn(): string {
        return 'address_id';
    }

    public function getModelClassName(): string {
        return ModelMPostContact::class;
    }
}


