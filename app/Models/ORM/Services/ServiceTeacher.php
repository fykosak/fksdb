<?php

namespace FKSDB\Models\ORM\Services;



use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelTeacher;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServiceTeacher extends AbstractServiceSingle {


    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_TEACHER, ModelTeacher::class);
    }

    public function store(?ModelTeacher $model, array $data): ModelTeacher {
        if (is_null($model)) {
            return $this->createNewModel($data);
        } else {
            $this->updateModel2($model, $data);
            return $this->refresh($model);
        }
    }
}
