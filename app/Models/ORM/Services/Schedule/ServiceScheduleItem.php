<?php

namespace FKSDB\Models\ORM\Services\Schedule;


use FKSDB\Models\ORM\DeprecatedLazyDBTrait;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\Models\ORM\Services\AbstractServiceSingle;

/**
 * Class ServiceScheduleItem
 * @author Michal Červeňák <miso@fykos.cz>
 * @method ModelScheduleItem|null findByPrimary($key)
 * @method ModelScheduleItem createNewModel(array $data)
 * @method ModelScheduleItem refresh(AbstractModelSingle $model)
 */
class ServiceScheduleItem extends AbstractServiceSingle {

    use DeprecatedLazyDBTrait;

    public function store(?ModelScheduleItem $group, array $data): ModelScheduleItem {
        if (is_null($group)) {
            return $this->createNewModel($data);
        } else {
            $this->updateModel2($group, $data);
            return $this->refresh($group);
        }
    }
}
