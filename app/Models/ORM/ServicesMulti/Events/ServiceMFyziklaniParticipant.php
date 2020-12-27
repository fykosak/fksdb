<?php

namespace FKSDB\Models\ORM\ServicesMulti\Events;

use FKSDB\Models\ORM\ModelsMulti\AbstractModelMulti;
use FKSDB\Models\ORM\IModel;
use FKSDB\Models\ORM\Services\Events\ServiceFyziklaniParticipant;
use FKSDB\Models\ORM\Services\ServiceEventParticipant;
use FKSDB\Models\ORM\ModelsMulti\Events\ModelMFyziklaniParticipant;
use FKSDB\Models\ORM\ServicesMulti\AbstractServiceMulti;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceMFyziklaniParticipant extends AbstractServiceMulti {

    public function __construct(ServiceEventParticipant $mainService, ServiceFyziklaniParticipant $joinedService) {
        parent::__construct($mainService, $joinedService, 'event_participant_id', ModelMFyziklaniParticipant::class);
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
