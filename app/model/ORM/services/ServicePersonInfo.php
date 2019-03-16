<?php

use ORM\IModel;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServicePersonInfo extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_PERSON_INFO;
    protected $modelClassName = 'FKSDB\ORM\Models\ModelPersonInfo';

    /**
     * @param null $data
     * @return AbstractModelSingle
     */
    public function createNew($data = null) {
        if ($data && isset($data['agreed']) && $data['agreed'] == '1') {
            $data['agreed'] = new DateTime();
        }

        return parent::createNew($data);
    }

    /**
     * @param IModel $model
     * @param array $data
     * @param bool $alive
     * @return mixed|void
     */
    public function updateModel(IModel $model, $data, $alive = true) {
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

