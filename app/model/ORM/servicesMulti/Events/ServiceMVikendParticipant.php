<?php

namespace ORM\ServicesMulti\Events;

use FKSDB\ORM\AbstractServiceMulti;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Services\Events\ServiceVikendParticipant;
use FKSDB\ORM\Services\ServiceEventParticipant;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceMVikendParticipant extends AbstractServiceMulti {

    protected $modelClassName = 'ORM\ModelsMulti\Events\ModelMVikendParticipant';
    protected $joiningColumn = 'event_participant_id';

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
     * @param IModel $model
     */
    public function dispose(IModel $model) {
        parent::dispose($model);
        $this->getMainService()->dispose($model->getMainModel());
    }

}
