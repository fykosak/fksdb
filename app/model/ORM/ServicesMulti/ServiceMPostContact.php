<?php

namespace FKSDB\ORM\ServicesMulti;

use FKSDB\ORM\ModelsMulti\AbstractModelMulti;
use FKSDB\ORM\DeprecatedLazyDBTrait;
use FKSDB\ORM\IModel;
use FKSDB\ORM\ModelsMulti\ModelMPostContact;
use FKSDB\ORM\Services\ServiceAddress;
use FKSDB\ORM\Services\ServicePostContact;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ServicePostContact getJoinedService()
 * @method ServiceAddress getMainService()
 */
class ServiceMPostContact extends AbstractServiceMulti {
    use DeprecatedLazyDBTrait;

    public function __construct(ServiceAddress $mainService, ServicePostContact $joinedService) {
        parent::__construct($mainService, $joinedService, 'address_id', ModelMPostContact::class);
    }

    /**
     * Delete post contact including the address.
     * @param IModel|AbstractModelMulti $model
     */
    public function dispose(IModel $model): void {
        parent::dispose($model);
        $this->getMainService()->dispose($model->getMainModel());
    }
}
