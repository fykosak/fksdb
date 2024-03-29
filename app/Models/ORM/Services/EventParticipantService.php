<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Services\Exceptions\DuplicateApplicationException;
use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Service\Service;

/**
 * @phpstan-extends Service<EventParticipantModel>
 */
final class EventParticipantService extends Service
{
    /**
     * @param EventParticipantModel|null $model
     */
    public function storeModel(array $data, ?Model $model = null): EventParticipantModel
    {
        try {
            return parent::storeModel($data, $model);
        } catch (\PDOException $exception) {
            if ($exception->getPrevious() && $exception->getPrevious()->getCode() == 23000) {
                throw new DuplicateApplicationException($model ? $model->person : null, $exception);
            }
            throw $exception;
        }
    }
}
