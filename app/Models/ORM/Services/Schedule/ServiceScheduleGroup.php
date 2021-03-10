<?php

namespace FKSDB\Models\ORM\Services\Schedule;

use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleGroup;
use Fykosak\NetteORM\AbstractService;

/**
 * Class ServiceScheduleGroup
 * @author Michal Červeňák <miso@fykos.cz>
 * @method ModelScheduleGroup|null findByPrimary($key)
 * @method ModelScheduleGroup createNewModel(array $data)
 * @method ModelScheduleGroup refresh(AbstractModel $model)
 */
class ServiceScheduleGroup extends AbstractService {

    public function store(?ModelScheduleGroup $group, array $data): ModelScheduleGroup {
        if (is_null($group)) {
            return $this->createNewModel($data);
        } else {
            $this->updateModel2($group, $data);
            return $this->refresh($group);
        }
    }
}
