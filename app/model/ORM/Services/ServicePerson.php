<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ModelPerson|null findByPrimary($key)
 * @method ModelPerson createNewModel(array $data)
 */
class ServicePerson extends AbstractServiceSingle {

    /**
     * ServicePerson constructor.
     * @param Context $connection
     * @param IConventions $conventions
     */
    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_PERSON, ModelPerson::class);
    }

    /**
     * Syntactic sugar.
     *
     * @param string $email
     * @return ModelPerson|null
     */
    public function findByEmail($email) {
        if (!$email) {
            return null;
        }
        /** @var ModelPerson|false $result */
        $result = $this->getTable()->where(':person_info.email', $email)->fetch();
        return $result ?: null;
    }

    /**
     * @param IModel|ModelPerson $model
     * @return void
     */
    public function save(IModel &$model) {
        if (is_null($model->gender)) {
            $model->inferGender();
        }
        parent::save($model);
    }

    /**
     * @param ModelPerson|null $person
     * @param array $data
     * @return ModelPerson
     */
    public function store($person, array $data): ModelPerson {
        if ($person) {
            $this->updateModel2($person, $data);
            return $person;
        } else {
            return $this->createNewModel($data);
        }
    }
}
