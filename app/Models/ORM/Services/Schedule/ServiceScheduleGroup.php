<?php

namespace FKSDB\Models\ORM\Services\Schedule;

use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleGroup;
use FKSDB\Models\ORM\Services\AbstractServiceSingle;

/**
 * Class ServiceScheduleGroup
 * @author Michal Červeňák <miso@fykos.cz>
 * @method ModelScheduleGroup|null findByPrimary($key)
 * @method ModelScheduleGroup createNewModel(array $data)
 * @method ModelScheduleGroup refresh(AbstractModelSingle $model)
 */
class ServiceScheduleGroup extends AbstractServiceSingle {



    public function store(?ModelScheduleGroup $group, array $data): ModelScheduleGroup {
        if (is_null($group)) {
            return $this->createNewModel($data);
        } else {
            $this->updateModel2($group, $data);
            return $this->refresh($group);
        }
    }
}
