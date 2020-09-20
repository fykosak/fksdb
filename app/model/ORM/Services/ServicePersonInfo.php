<?php

namespace FKSDB\ORM\Services;

use DateTime;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\DeprecatedLazyDBTrait;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\ModelPersonInfo;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ModelPersonInfo refresh(AbstractModelSingle $model)
 * @method ModelPersonInfo findByPrimary($key)
 */
class ServicePersonInfo extends AbstractServiceSingle {
    use DeprecatedLazyDBTrait;

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_PERSON_INFO, ModelPersonInfo::class);
    }

    public function createNewModel(array $data): ModelPersonInfo {
        if (isset($data['agreed']) && $data['agreed'] == '1') {
            $data['agreed'] = new DateTime();
        }
        return parent::createNewModel($data);
    }

    /**
     * @param IModel|AbstractModelSingle|ModelPersonInfo $model
     * @param array $data
     * @return bool
     */
    public function updateModel2(IModel $model, array $data): bool {
        if (isset($data['agreed'])) {
            if ($data['agreed'] == '1') {
                $data['agreed'] = new DateTime();
            } elseif ($data['agreed'] == '0') {
                unset($data['agreed']);
            }
        }
        return parent::updateModel2($model, $data);
    }

    public function store(ModelPerson $person, ?ModelPersonInfo $info, array $data): ModelPersonInfo {
        if ($info) {
            $this->updateModel2($info, $data);
            return $this->refresh($info);
        } else {
            $data['person_id'] = $person->person_id;
            return $this->createNewModel($data);
        }
    }
}
