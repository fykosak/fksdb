<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Links;

use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\EventParticipantModel;

class ApplicationEditLink extends LinkFactory
{
    public function getText(): string
    {
        return _('Edit application');
    }

    /**
     * @param EventParticipantModel $model
     */
    protected function getDestination(Model $model): string
    {
        if ($model->event->isTeamEvent()) {
            return ':Event:TeamApplication:edit';
        } else {
            return ':Public:Application:default';
        }
    }

    /**
     * @param EventParticipantModel $model
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
