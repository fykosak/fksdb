<?php

use Nette\InvalidArgumentException;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceMPostContact extends AbstractServiceMulti {

    protected static $staticMainServiceName = 'ServiceAddress';
    protected static $staticJoinedServiceName = 'ServicePostContact';
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

}

?>
