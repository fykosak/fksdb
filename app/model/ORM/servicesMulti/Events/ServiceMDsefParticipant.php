<?php

namespace ORM\ServicesMulti\Events;

use FKSDB\ORM\AbstractModelMulti;
use FKSDB\ORM\AbstractServiceMulti;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Services\Events\ServiceDsefParticipant;
use FKSDB\ORM\Services\ServiceEventParticipant;
use ORM\ModelsMulti\Events\ModelMDsefParticipant;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceMDsefParticipant extends AbstractServiceMulti {

    /**
     * ServiceMDsefParticipant constructor.
     * @param ServiceEventParticipant $mainService
     * @param ServiceDsefParticipant $joinedService
     */
    public function __construct(ServiceEventParticipant $mainService, ServiceDsefParticipant $joinedService) {
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
        return 'event_participant_id';
    }

    public function getModelClassName(): string {
        return ModelMDsefParticipant::class;
    }
}
