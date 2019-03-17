<?php

namespace ORM\ServicesMulti\Events;

use AbstractServiceMulti;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Services\Events\ServiceTsafParticipant;
use FKSDB\ORM\Services\ServiceEventParticipant;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceMTsafParticipant extends AbstractServiceMulti {

    protected $modelClassName = 'ORM\ModelsMulti\Events\ModelMTsafParticipant';
    protected $joiningColumn = 'event_participant_id';

    /**
     * ServiceMTsafParticipant constructor.
     * @param ServiceEventParticipant $mainService
     * @param ServiceTsafParticipant $joinedService
     */
    public function __construct(ServiceEventParticipant $mainService, ServiceTsafParticipant $joinedService) {
        parent::__construct($mainService, $joinedService);
    }

    /**
     * @param \FKSDB\ORM\IModel $model
     */
    public function dispose(IModel $model) {
        parent::dispose($model);
        $this->getMainService()->dispose($model->getMainModel());
    }

}

