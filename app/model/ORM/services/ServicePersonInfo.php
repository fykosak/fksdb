<?php

use ORM\IModel;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServicePersonInfo extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_PERSON_INFO;
    protected $modelClassName = 'ModelPersonInfo';

    public function createNew($data = null) {
        if ($data && isset($data['agreed']) && $data['agreed'] == '1') {
            $data['agreed'] = new DateTime();
        }

        return parent::createNew($data);
    }

    public function updateModel(IModel $model, $data) {
        if (isset($data['agreed'])) {
            if ($data['agreed'] == '1') {
                $data['agreed'] = new DateTime();
            } else if ($data['agreed'] == '0') {
                unset($data['agreed']);
            }
        }
        return parent::updateModel($model, $data);
    }

}

