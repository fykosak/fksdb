<?php

namespace FKSDB\ORM\ServicesMulti\Events;

use FKSDB\ORM\AbstractModelMulti;
use FKSDB\ORM\AbstractServiceMulti;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Services\Events\ServiceFyziklaniParticipant;
use FKSDB\ORM\Services\ServiceEventParticipant;
use FKSDB\ORM\ModelsMulti\Events\ModelMFyziklaniParticipant;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceMFyziklaniParticipant extends AbstractServiceMulti {

    /**
     * ServiceMFyziklaniParticipant constructor.
     * @param ServiceEventParticipant $mainService
     * @param ServiceFyziklaniParticipant $joinedService
     */
    public function __construct(ServiceEventParticipant $mainService, ServiceFyziklaniParticipant $joinedService) {
        parent::__construct($mainService, $joinedService, 'event_participant_id', ModelMFyziklaniParticipant::class);
    }

    /**
     * Delete post contact including the address.
     * @param IModel|AbstractModelMulti $model
     */
    public function dispose(IModel $model) {
        parent::dispose($model);
        $this->getMainService()->dispose($model->getMainModel());
    }
}
