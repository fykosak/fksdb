<?php

namespace FKSDB\Model\ORM\ServicesMulti\Events;

use FKSDB\Model\ORM\ModelsMulti\AbstractModelMulti;
use FKSDB\Model\ORM\IModel;
use FKSDB\Model\ORM\Services\Events\ServiceFyziklaniParticipant;
use FKSDB\Model\ORM\Services\ServiceEventParticipant;
use FKSDB\Model\ORM\ModelsMulti\Events\ModelMFyziklaniParticipant;
use FKSDB\Model\ORM\ServicesMulti\AbstractServiceMulti;

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
