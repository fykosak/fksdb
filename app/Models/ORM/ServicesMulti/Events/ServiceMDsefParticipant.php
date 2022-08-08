<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\ServicesMulti\Events;

use Fykosak\NetteORM\Exceptions\ModelException;
use FKSDB\Models\ORM\ModelsMulti\ModelMulti;
use FKSDB\Models\ORM\Services\Events\ServiceDsefParticipant;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\ORM\ModelsMulti\Events\ModelMDsefParticipant;
use FKSDB\Models\ORM\ServicesMulti\ServiceMulti;

/**
 * @deprecated
 */
class ServiceMDsefParticipant extends ServiceMulti
{

    public function __construct(EventParticipantService $mainService, ServiceDsefParticipant $joinedService)
    {
        parent::__construct($mainService, $joinedService, 'event_participant_id', ModelMDsefParticipant::class);
    }

    /**
     * Delete post contact including the address.
     * @throws ModelException
     */
    public function disposeModel(ModelMulti $model): void
    {
        parent::disposeModel($model);
        $this->mainService->disposeModel($model->mainModel);
    }
}
