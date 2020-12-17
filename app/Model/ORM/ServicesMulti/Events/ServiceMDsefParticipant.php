<?php

namespace FKSDB\Model\ORM\ServicesMulti\Events;

use FKSDB\Model\ORM\ModelsMulti\AbstractModelMulti;
use FKSDB\Model\ORM\IModel;
use FKSDB\Model\ORM\Services\Events\ServiceDsefParticipant;
use FKSDB\Model\ORM\Services\ServiceEventParticipant;
use FKSDB\Model\ORM\ModelsMulti\Events\ModelMDsefParticipant;
use FKSDB\Model\ORM\ServicesMulti\AbstractServiceMulti;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceMDsefParticipant extends AbstractServiceMulti {

    public function __construct(ServiceEventParticipant $mainService, ServiceDsefParticipant $joinedService) {
        parent::__construct($mainService, $joinedService, 'event_participant_id', ModelMDsefParticipant::class);
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
