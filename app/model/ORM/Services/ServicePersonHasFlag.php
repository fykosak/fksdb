<?php

namespace FKSDB\ORM\Services;

use DateTime;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\IModel;
use Nette\ArrayHash;

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServicePersonHasFlag extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_PERSON_HAS_FLAG;
    protected $modelClassName = 'FKSDB\ORM\Models\ModelPersonHasFlag';

    /**
     * @param null $data
     * @return \FKSDB\ORM\AbstractModelSingle
     */
    public function createNew($data = null) {
        if ($data === null) {
            $data = new ArrayHash();
        }
        $data['modified'] = new DateTime();
        return parent::createNew($data);
    }

    /**
     * @param IModel $model
     * @param array $data
     * @param bool $alive
     * @return mixed|void
     */
    public function updateModel(IModel $model, $data, $alive = true) {
        if ($data === null) {
            $data = new ArrayHash();
        }
        $data['modified'] = new DateTime();
        return parent::updateModel($model, $data);
    }

}
