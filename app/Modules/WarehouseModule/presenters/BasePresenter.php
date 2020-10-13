<?php

namespace FKSDB\Modules\WarehouseModule;

use FKSDB\Exceptions\ContestNotFoundException;
use FKSDB\Modules\Core\BasePresenter as CoreBasePresenter;
use FKSDB\ORM\Models\ModelContest;

abstract class BasePresenter extends CoreBasePresenter {
    /**
     * @var int
     * @persistent
     */
    public $contestId;

    private ModelContest $contest;

    protected function getContest(): ModelContest {
        if (!isset($this->contest)) {
            $contest = $this->serviceContest->findByPrimary($this->contestId);
            if (is_null($contest)) {
                throw new ContestNotFoundException($this->contestId);
            }
            $this->contest = $contest;
        }
        return $this->contest;
    }

}
