<?php

namespace FKSDB\ORM\ServicesMulti\Events;

use FKSDB\ORM\AbstractModelMulti;
use FKSDB\ORM\AbstractServiceMulti;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Services\Events\ServiceVikendParticipant;
use FKSDB\ORM\Services\ServiceEventParticipant;
use FKSDB\ORM\ModelsMulti\Events\ModelMVikendParticipant;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceMVikendParticipant extends AbstractServiceMulti {
    /**
     * ServiceMVikendParticipant constructor.
     * @param ServiceEventParticipant $mainService
     * @param ServiceVikendParticipant $joinedService
     */
    public function __construct(ServiceEventParticipant $mainService, ServiceVikendParticipant $joinedService) {
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
        return ModelMVikendParticipant::class;
    }
}
