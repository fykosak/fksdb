<?php

namespace FKSDB\Model\ORM\Services\Schedule;

use FKSDB\Model\ORM\DbNames;
use FKSDB\Model\ORM\DeprecatedLazyDBTrait;
use FKSDB\Model\ORM\Models\AbstractModelSingle;
use FKSDB\Model\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\Model\ORM\Services\AbstractServiceSingle;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * Class ServiceScheduleItem
 * @author Michal Červeňák <miso@fykos.cz>
 * @method ModelScheduleItem|null findByPrimary($key)
 * @method ModelScheduleItem createNewModel(array $data)
 * @method ModelScheduleItem refresh(AbstractModelSingle $model)
 */
class ServiceScheduleItem extends AbstractServiceSingle {
    use DeprecatedLazyDBTrait;

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_SCHEDULE_ITEM, ModelScheduleItem::class);
    }

    public function store(?ModelScheduleItem $group, array $data): ModelScheduleItem {
        if (is_null($group)) {
            return $this->createNewModel($data);
        } else {
            $this->updateModel2($group, $data);
            return $this->refresh($group);
        }
    }
}
