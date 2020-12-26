<?php

namespace FKSDB\Models\ORM\ServicesMulti;

use FKSDB\Models\ORM\ModelsMulti\AbstractModelMulti;
use FKSDB\Models\ORM\IModel;
use FKSDB\Models\ORM\ModelsMulti\ModelMPostContact;
use FKSDB\Models\ORM\Services\ServiceAddress;
use FKSDB\Models\ORM\Services\ServicePostContact;
use Nette\InvalidStateException;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ServicePostContact getJoinedService()
 * @method ServiceAddress getMainService()
 */
class ServiceMPostContact extends AbstractServiceMulti {

    public function __construct(ServiceAddress $mainService, ServicePostContact $joinedService) {
        parent::__construct($mainService, $joinedService, 'address_id', ModelMPostContact::class);
    }

    /**
     * Delete post contact including the address.
     * @param IModel|AbstractModelMulti $model
     * @throws InvalidStateException
     */
    public function dispose(IModel $model): void {
        parent::dispose($model);
        $this->getMainService()->dispose($model->getMainModel());
    }
}
