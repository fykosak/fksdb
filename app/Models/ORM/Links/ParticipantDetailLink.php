<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Links;

use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\EventParticipantModel;

class ParticipantDetailLink extends LinkFactory
{

    public function getText(): string
    {
        return _('Applications detail');
    }

    /**
     * @param EventParticipantModel $model
     */
    protected function getDestination(Model $model): string
    {
        return ':Event:Application:detail';
    }

    /**
     * @param EventParticipantModel $model
     */
    protected function prepareParams(Model $model): array
    {
        return [
            'eventId' => $model->event_id,
            'id' => $model->event_participant_id,
        ];
    }
}
