<?php

namespace ORM\ServicesMulti\Events;

use AbstractServiceMulti;
use FKSDB\ORM\Services\Events\ServiceFyziklaniParticipant;
use ORM\IModel;
use ServiceEventParticipant;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceMFyziklaniParticipant extends AbstractServiceMulti {

    protected $modelClassName = 'ORM\ModelsMulti\Events\ModelMFyziklaniParticipant';
    protected $joiningColumn = 'event_participant_id';

    /**
     * ServiceMFyziklaniParticipant constructor.
     * @param ServiceEventParticipant $mainService
     * @param \FKSDB\ORM\Services\Events\ServiceFyziklaniParticipant $joinedService
     */
    public function __construct(ServiceEventParticipant $mainService, \FKSDB\ORM\Services\Events\ServiceFyziklaniParticipant $joinedService) {
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
