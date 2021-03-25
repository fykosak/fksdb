<?php

namespace FKSDB\Models\ORM\Services;

use DateTime;
use Fykosak\NetteORM\AbstractService;
use Fykosak\NetteORM\AbstractModel;

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServicePersonHasFlag extends AbstractService {

    public function createNewModel(array $data): AbstractModel {
        $data['modified'] = new DateTime();
        return parent::createNewModel($data);
    }

    public function updateModel2(AbstractModel $model, array $data): bool {
        $data['modified'] = new DateTime();
        return parent::updateModel2($model, $data);
    }
}
