<?php

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\Exceptions\ModelException;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelEventOrg;
use FKSDB\Models\ORM\Services\Exceptions\DuplicateOrgException;
use FKSDB\Models\ORM\Tables\TypedTableSelection;

/**
 * Class ServiceEventOrg
 * @author Michal Červeňák <miso@fykos.cz>
 * @method ModelEventOrg refresh(AbstractModelSingle $model)
 */
class ServiceEventOrg extends AbstractServiceSingle {

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
