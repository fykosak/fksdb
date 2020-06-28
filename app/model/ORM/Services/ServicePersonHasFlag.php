<?php

namespace FKSDB\ORM\Services;

use DateTime;
use FKSDB\Exceptions\ModelException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\ModelPersonHasFlag;
use Nette\Utils\ArrayHash;

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServicePersonHasFlag extends AbstractServiceSingle {

    public function getModelClassName(): string {
        return ModelPersonHasFlag::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_PERSON_HAS_FLAG;
    }

    /**
     * @param null $data
     * @return AbstractModelSingle
     * @throws ModelException
     * @deprecated
     */
    public function createNew($data = null) {
        if ($data === null) {
            $data = new ArrayHash();
        }
        $data['modified'] = new DateTime();
        return parent::createNew($data);
    }

    public function createNewModel(array $data): IModel {
        $data['modified'] = new DateTime();
        return parent::createNewModel($data);
    }

    /**
     * @param IModel $model
     * @param array $data
     * @param bool $alive
     * @return void
     * @throws \Exception
     */
    public function updateModel(IModel $model, $data, $alive = true) {
        if ($data === null) {
            $data = new ArrayHash();
        }
        $data['modified'] = new DateTime();
        parent::updateModel($model, $data);
    }

    public function updateModel2(AbstractModelSingle $model, array $data): bool {
        $data['modified'] = new DateTime();
        return parent::updateModel2($model, $data);
    }
}
