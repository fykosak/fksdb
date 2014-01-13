<?php

namespace ORM\ServicesMulti\Events;

use AbstractServiceMulti;
use ORM\IModel;
use ORM\ModelsMulti\Events\ModelMFyziklaniParticipant;
use ORM\Services\Events\ServiceFyziklaniParticipant;
use ServiceEventParticipant;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceMFyziklaniParticipant extends AbstractServiceMulti {

    protected $modelClassName = 'ORM\ModelsMulti\Events\ModelMFyziklaniParticipant';

    public function __construct(ServiceEventParticipant $mainService, ServiceFyziklaniParticipant $joinedService) {
        parent::__construct($mainService, $joinedService);
    }

    /**
     * Delete post contact including the address.
     * @param ModelMFyziklaniParticipant $model
     */
    public function dispose(IModel $model) {
        parent::dispose($model);
        $this->getMainService()->dispose($model->getMainModel());
    }

    /**
     * 
     * @param int $key ID of the post contact
     * @return ModelMFyziklaniParticipant|null
     */
    public function findByPrimary($key) {
        $joinedModel = $this->getJoinedService()->findByPrimary($key);
        if (!$joinedModel) {
            return null;
        }
        $mainModel = $joinedModel->getMainModel();
        return $this->composeModel($mainModel, $joinedModel);
    }

}

?>
