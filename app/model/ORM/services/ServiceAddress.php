<?php

use Nette\InvalidArgumentException;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceAddress extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_ADDRESS;
    protected $modelClassName = 'ModelAddress';

    public function save(\AbstractModelSingle &$model) {
        if (!$model instanceof ModelAddress) {
            throw new InvalidArgumentException("Expecting ModelAddress, got '" . get_class($model) . "'");
        }
        $model->inferRegion();
        parent::save($model);
    }

}

