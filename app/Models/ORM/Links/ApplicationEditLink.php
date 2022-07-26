<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Links;

use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\ModelEventParticipant;

class ApplicationEditLink extends LinkFactory
{

    public function getText(): string
    {
        return _('Edit');
    }

    /**
     * @param ModelEventParticipant $model
     */
    protected function getDestination(Model $model): string
    {
        return ':Public:Application:default';
    }

    /**
     * @param ModelEventParticipant $model
     */
    protected function prepareParams(Model $model): array
    {
        if ($model->event->isTeamEvent()) {
            return [
                'eventId' => $model->event_id,
                'id' => $model->getFyziklaniTeam()->e_fyziklani_team_id,
            ];
        } else {
            return [
                'eventId' => $model->event_id,
                'id' => $model->event_participant_id,
            ];
        }
    }
}
