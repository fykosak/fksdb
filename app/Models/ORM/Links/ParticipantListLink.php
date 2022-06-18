<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Links;

use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\ModelEvent;

class ParticipantListLink extends LinkFactory
{

    public function getText(): string
    {
        return _('List of applications');
    }

    /**
     * @param ModelEvent $model
     */
    protected function getDestination(Model $model): string
    {
        if ($model->isTeamEvent()) {
            return ':Event:TeamApplication:list';
        } else {
            return ':Event:Application:list';
        }
    }

    /**
     * @param ModelEvent $model
     */
    protected function prepareParams(Model $model): array
    {
        return [
            'eventId' => $model->event_id,
        ];
    }
}
