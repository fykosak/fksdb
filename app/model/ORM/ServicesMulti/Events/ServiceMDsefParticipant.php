<?php

namespace FKSDB\ORM\ServicesMulti\Events;

use FKSDB\ORM\AbstractModelMulti;
use FKSDB\ORM\AbstractServiceMulti;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Services\Events\ServiceDsefParticipant;
use FKSDB\ORM\Services\ServiceEventParticipant;
use FKSDB\ORM\ModelsMulti\Events\ModelMDsefParticipant;

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
