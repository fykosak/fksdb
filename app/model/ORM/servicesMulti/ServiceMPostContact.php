<?php

use FKSDB\ORM\AbstractServiceMulti;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Services\ServiceAddress;
use FKSDB\ORM\Services\ServicePostContact;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceMPostContact extends AbstractServiceMulti {

    protected $modelClassName = 'ModelMPostContact';
    protected $joiningColumn = 'address_id';

    /**
     * ServiceMPostContact constructor.
     * @param ServiceAddress $mainService
     * @param ServicePostContact $joinedService
     */
    public function __construct(ServiceAddress $mainService, ServicePostContact $joinedService) {
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

    /**
     * @param IModel $model
     * @param $data
     * @param bool $alive
     * @return mixed|void
     */
    public function updateModel(IModel $model, $data, $alive = true) {
        if (!$model instanceof $this->modelClassName) {
            throw new InvalidArgumentException('Service for class ' . $this->modelClassName . ' cannot store ' . get_class($model));
        }
        $this->getMainService()->updateModel2($model->getMainModel(), $data);
        $this->getJoinedService()->updateModel2($model->getJoinedModel(), $data);
    }

}


