<?php

namespace FKSDB\Models\ORM\Services\Schedule;

use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleItem;
use Fykosak\NetteORM\AbstractService;

/**
 * Class ServiceScheduleItem
 * @author Michal Červeňák <miso@fykos.cz>
 * @method ModelScheduleItem|null findByPrimary($key)
 * @method ModelScheduleItem createNewModel(array $data)
 * @method ModelScheduleItem refresh(AbstractModel $model)
 */
class ServiceScheduleItem extends AbstractService {

    public function store(?ModelScheduleItem $group, array $data): ModelScheduleItem {
        if (is_null($group)) {
            return $this->createNewModel($data);
        } else {
            $this->updateModel2($group, $data);
            return $this->refresh($group);
        }
    }
}
