<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\ServicesMulti\Events;

use Fykosak\NetteORM\Exceptions\ModelException;
use FKSDB\Models\ORM\ModelsMulti\ModelMulti;
use FKSDB\Models\ORM\Services\Fyziklani\ParticipantService;
use FKSDB\Models\ORM\Services\ServiceEventParticipant;
use FKSDB\Models\ORM\ModelsMulti\Events\ModelMFyziklaniParticipant;
use FKSDB\Models\ORM\ServicesMulti\ServiceMulti;

class ServiceMFyziklaniParticipant extends ServiceMulti
{

    public function __construct(ServiceEventParticipant $mainService, ParticipantService $joinedService)
    {
        parent::__construct($mainService, $joinedService, 'event_participant_id', ModelMFyziklaniParticipant::class);
    }

    /**
     * Delete post contact including the address.
     * @throws ModelException
     */
    public function dispose(ModelMulti $model): void
    {
        parent::dispose($model);
        $this->mainService->dispose($model->mainModel);
    }
}
