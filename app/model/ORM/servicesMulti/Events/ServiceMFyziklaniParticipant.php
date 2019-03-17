<?php

namespace ORM\ServicesMulti\Events;

use AbstractServiceMulti;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Services\Events\ServiceFyziklaniParticipant;
use FKSDB\ORM\Services\ServiceEventParticipant;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceMFyziklaniParticipant extends AbstractServiceMulti {

    protected $modelClassName = 'ORM\ModelsMulti\Events\ModelMFyziklaniParticipant';
    protected $joiningColumn = 'event_participant_id';

    /**
     * ServiceMFyziklaniParticipant constructor.
     * @param \FKSDB\ORM\Services\ServiceEventParticipant $mainService
     * @param ServiceFyziklaniParticipant $joinedService
     */
    public function __construct(ServiceEventParticipant $mainService, ServiceFyziklaniParticipant $joinedService) {
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
