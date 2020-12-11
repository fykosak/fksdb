<?php

namespace FKSDB\Model\ORM\Services;

use DateTime;
use FKSDB\Model\ORM\DbNames;
use FKSDB\Model\ORM\DeprecatedLazyDBTrait;
use FKSDB\Model\ORM\Models\AbstractModelSingle;
use FKSDB\Model\ORM\Models\ModelPerson;
use FKSDB\Model\ORM\Models\ModelPersonInfo;
use Fykosak\Utils\ORM\AbstractModel;
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
     * @param AbstractModel|ModelPersonInfo $model
     * @param array $data
     * @return bool
     */
    public function updateModel2(AbstractModel $model, array $data): bool {
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
