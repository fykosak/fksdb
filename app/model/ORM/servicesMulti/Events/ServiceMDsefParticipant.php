<?php

namespace ORM\ServicesMulti\Events;

use FKSDB\ORM\AbstractServiceMulti;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Services\Events\ServiceDsefParticipant;
use FKSDB\ORM\Services\ServiceEventParticipant;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceMDsefParticipant extends AbstractServiceMulti {

    protected $modelClassName = 'ORM\ModelsMulti\Events\ModelMDsefParticipant';
    protected $joiningColumn = 'event_participant_id';

    /**
     * ServiceMDsefParticipant constructor.
     * @param \FKSDB\ORM\Services\ServiceEventParticipant $mainService
     * @param \FKSDB\ORM\Services\Events\ServiceDsefParticipant $joinedService
     */
    public function __construct(ServiceEventParticipant $mainService, ServiceDsefParticipant $joinedService) {
        parent::__construct($mainService, $joinedService);
    }

    /**
     * Delete post contact including the address.
     * @param IModel $model
     */
    public function dispose(IModel $model) {
        parent::dispose($model);
        $this->getMainService()->dispose($model->getMainModel());
    }

}
