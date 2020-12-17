<?php

namespace FKSDB\Model\ORM\Services;

use FKSDB\Model\ORM\DbNames;
use FKSDB\Model\ORM\Models\AbstractModelSingle;
use FKSDB\Model\ORM\Models\ModelPerson;
use FKSDB\Model\ORM\Models\ModelPersonHistory;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ModelPersonHistory createNewModel(array $data)
 * @method ModelPersonHistory refresh(AbstractModelSingle $model)
 */
class ServicePersonHistory extends AbstractServiceSingle {

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_PERSON_HISTORY, ModelPersonHistory::class);
    }

    public function store(ModelPerson $person, ?ModelPersonHistory $history, array $data, int $acYear): ModelPersonHistory {
        if ($history) {
            $this->updateModel2($history, $data);
            return $this->refresh($history);
        } else {
            return $this->createNewModel(array_merge($data, [
                'ac_year' => $acYear,
                'person_id' => $person->person_id,
            ]));
        }
    }
}
