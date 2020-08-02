<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\ModelPersonHistory;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ModelPersonHistory createNewModel(array $data)
 * @method ModelPersonHistory refresh(AbstractModelSingle $model)
 */
class ServicePersonHistory extends AbstractServiceSingle {
    /**
     * ServicePersonHistory constructor.
     * @param Context $connection
     * @param IConventions $conventions
     */
    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_PERSON_HISTORY, ModelPersonHistory::class);
    }

    /**
     * @param ModelPerson $person
     * @param ModelPersonHistory|null $history
     * @param array $data
     * @param int $acYear
     * @return ModelPersonHistory
     */
    public function store(ModelPerson $person, $history, array $data, int $acYear): ModelPersonHistory {
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
