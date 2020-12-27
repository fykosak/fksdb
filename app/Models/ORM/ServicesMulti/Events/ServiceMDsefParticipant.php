<?php

namespace FKSDB\Models\ORM\ServicesMulti\Events;

use FKSDB\Models\ORM\ModelsMulti\AbstractModelMulti;
use FKSDB\Models\ORM\IModel;
use FKSDB\Models\ORM\Services\Events\ServiceDsefParticipant;
use FKSDB\Models\ORM\Services\ServiceEventParticipant;
use FKSDB\Models\ORM\ModelsMulti\Events\ModelMDsefParticipant;
use FKSDB\Models\ORM\ServicesMulti\AbstractServiceMulti;

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
