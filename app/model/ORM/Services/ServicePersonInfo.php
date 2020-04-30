<?php

namespace FKSDB\ORM\Services;

use DateTime;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\ModelPersonInfo;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServicePersonInfo extends AbstractServiceSingle {

    /**
     * @return string
     */
    public function getModelClassName(): string {
        return ModelPersonInfo::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_PERSON_INFO;
    }

    /**
     * @param null $data
     * @return AbstractModelSingle
     * @throws \Exception
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
     * @throws \Exception
     */
    public function updateModel(IModel $model, $data, $alive = true) {
        if (isset($data['agreed'])) {
            if ($data['agreed'] == '1') {
                $data['agreed'] = new DateTime();
            } elseif ($data['agreed'] == '0') {
                unset($data['agreed']);
            }
        }
        return parent::updateModel($model, $data);
    }

    /**
     * @param IModel|AbstractModelSingle|ModelPersonInfo $model
     * @param array $data
     * @param bool $alive
     * @return mixed|void
     * @throws \Exception
     */
    public function updateModel2(AbstractModelSingle $model, $data = null, $alive = true) {
        if (isset($data['agreed'])) {
            if ($data['agreed'] == '1') {
                $data['agreed'] = new DateTime();
            } elseif ($data['agreed'] == '0') {
                unset($data['agreed']);
            }
        }
        return parent::updateModel2($model, $data);
    }
}
