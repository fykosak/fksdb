<?php

namespace ORM\ServicesMulti\Events;

use FKSDB\ORM\AbstractServiceMulti;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Services\Events\ServiceSousParticipant;
use FKSDB\ORM\Services\ServiceEventParticipant;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @deprecated
 */
class ServiceMSousParticipant extends AbstractServiceMulti {

    protected $modelClassName = 'ORM\ModelsMulti\Events\ModelMSousParticipant';
    protected $joiningColumn = 'event_participant_id';

    /**
     * ServiceMSousParticipant constructor.
     * @param ServiceEventParticipant $mainService
     * @param ServiceSousParticipant $joinedService
     */
    public function __construct(ServiceEventParticipant $mainService, ServiceSousParticipant $joinedService) {
        parent::__construct($mainService, $joinedService);
    }

    /**
     * Delete post contact including the address.
     * @param \FKSDB\ORM\IModel $model
     */
    public function dispose(IModel $model) {
        parent::dispose($model);
        $this->getMainService()->dispose($model->getMainModel());
    }

}
