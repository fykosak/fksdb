<?php

use Nette\ArrayHash;
use ORM\IModel;

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServicePersonHasFlag extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_PERSON_HAS_FLAG;
    protected $modelClassName = 'ModelPersonHasFlag';

    public function createNew($data = null) {
        if ($data === null) {
            $data = new ArrayHash();
        }
        $data['modified'] = new DateTime();
        return parent::createNew($data);
    }

    public function updateModel(IModel $model, $data) {
        if ($data === null) {
            $data = new ArrayHash();
        }
        $data['modified'] = new DateTime();
        return parent::updateModel($model, $data);
    }

}
