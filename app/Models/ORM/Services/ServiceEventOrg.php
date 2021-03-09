<?php

namespace FKSDB\Models\ORM\Services;

use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\ModelEventOrg;
use FKSDB\Models\ORM\Services\Exceptions\DuplicateOrgException;
use Fykosak\NetteORM\AbstractService;
/**
 * Class ServiceEventOrg
 * @author Michal Červeňák <miso@fykos.cz>
 * @method ModelEventOrg refresh(AbstractModel $model)
 */
class ServiceEventOrg extends AbstractService {

    public function createNewModel(array $data): ModelEventOrg {
        try {
            return parent::createNewModel($data);
        } catch (ModelException $exception) {
            if ($exception->getPrevious() && $exception->getPrevious()->getCode() == 23000) {
                throw new DuplicateOrgException(null, $exception);
            }
            throw $exception;
        }
    }

    public function store(?ModelEventOrg $model, array $data): ModelEventOrg {
        if (is_null($model)) {
            return $this->createNewModel($data);
        } else {
            $this->updateModel2($model, $data);
            return $this->refresh($model);
        }
    }
}
