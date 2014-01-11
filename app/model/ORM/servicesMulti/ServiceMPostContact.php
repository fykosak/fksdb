<?php

use Nette\InvalidArgumentException;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceMPostContact extends AbstractServiceMulti {

    protected $modelClassName = 'ModelMPostContact';

    public function __construct(ServiceAddress $mainService, ServicePostContact $joinedService) {
        parent::__construct($mainService, $joinedService);
    }

    /**
     * Delete post contact including the address.
     * @param ModelMPostContact $model
     */
    public function dispose(\AbstractModelMulti $model) {
        if (!$model instanceof ModelMPostContact) {
            throw new InvalidArgumentException("Expecting ModelMPostContact, got '" . get_class($model) . "'");
        }
        parent::dispose($model);
        $this->getMainService()->dispose($model->getMainModel());
    }

    /**
     * 
     * @param int $key ID of the post contact
     * @return ModelMPostContact|null
     */
    public function findByPrimary($key) {
        $joinedModel = $this->getJoinedService()->findByPrimary($key);
        if (!$joinedModel) {
            return null;
        }
        $mainModel = $joinedModel->getAddress();
        return $this->composeModel($mainModel, $joinedModel);
    }

}

?>
