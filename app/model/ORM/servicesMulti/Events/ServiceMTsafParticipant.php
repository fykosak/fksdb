<?php

namespace ORM\ServicesMulti\Events;

use FKSDB\ORM\AbstractModelMulti;
use FKSDB\ORM\AbstractServiceMulti;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Services\Events\ServiceTsafParticipant;
use FKSDB\ORM\Services\ServiceEventParticipant;
use ORM\ModelsMulti\Events\ModelMTsafParticipant;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceMTsafParticipant extends AbstractServiceMulti {
    /**
     * ServiceMTsafParticipant constructor.
     * @param ServiceEventParticipant $mainService
     * @param ServiceTsafParticipant $joinedService
     */
    public function __construct(ServiceEventParticipant $mainService, ServiceTsafParticipant $joinedService) {
        parent::__construct($mainService, $joinedService);
    }

    /**
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
        return ModelMTsafParticipant::class;
    }
}

