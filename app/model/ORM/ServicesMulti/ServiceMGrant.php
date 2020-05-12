<?php

namespace FKSDB\ORM\ServicesMulti;

use FKSDB\ORM\AbstractServiceMulti;
use FKSDB\ORM\Services\ServiceGrant;
use FKSDB\ORM\Services\ServiceRole;
use FKSDB\ORM\ModelsMulti\ModelMGrant;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceMGrant extends AbstractServiceMulti {

    /**
     * ServiceMGrant constructor.
     * @param ServiceRole $mainService
     * @param ServiceGrant $joinedService
     */
    public function __construct(ServiceRole $mainService, ServiceGrant $joinedService) {
        parent::__construct($mainService, $joinedService);
    }

    public function getJoiningColumn(): string {
        return 'role_id';
    }

    public function getModelClassName(): string {
        return ModelMGrant::class;
    }
}
