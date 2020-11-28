<?php

namespace FKSDB\ORM\Services\Schedule;

use FKSDB\ORM\Services\AbstractServiceSingle;
use FKSDB\ORM\Models\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\DeprecatedLazyDBTrait;
use FKSDB\ORM\Models\Schedule\ModelScheduleGroup;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * Class ServiceScheduleGroup
 * @author Michal Červeňák <miso@fykos.cz>
 * @method ModelScheduleGroup|null findByPrimary($key)
 * @method ModelScheduleGroup createNewModel(array $data)
 * @method ModelScheduleGroup refresh(AbstractModelSingle $model)
 */
class ServiceScheduleGroup extends AbstractServiceSingle {
    use DeprecatedLazyDBTrait;

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_SCHEDULE_GROUP, ModelScheduleGroup::class);
    }

    public function store(?ModelScheduleGroup $group, array $data): ModelScheduleGroup {
        if (is_null($group)) {
            return $this->createNewModel($data);
        } else {
            $this->updateModel2($group, $data);
            return $this->refresh($group);
        }
    }
}
