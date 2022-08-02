<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use Fykosak\NetteORM\Exceptions\ModelException;
use FKSDB\Models\ORM\Models\EventOrgModel;
use FKSDB\Models\ORM\Services\Exceptions\DuplicateOrgException;
use Fykosak\NetteORM\Service;

class ServiceEventOrg extends Service
{

    public function createNewModel(array $data): EventOrgModel
    {
        try {
            return parent::createNewModel($data);
        } catch (ModelException $exception) {
            if ($exception->getPrevious() && $exception->getPrevious()->getCode() == 23000) {
                throw new DuplicateOrgException(null, $exception);
            }
            throw $exception;
        }
    }
}
