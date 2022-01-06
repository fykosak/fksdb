<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Models\ORM\Links;

use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\ModelEventParticipant;

class ParticipantDetailLink extends LinkFactory
{

    public function getText(): string
    {
        return _('Detail');
    }

    /**
     * @param ModelEventParticipant|AbstractModel $model
     */
    protected function getDestination(AbstractModel $model): string
    {
        if ($model->getEvent()->isTeamEvent()) {
            return ':Event:TeamApplication:detail';
        } else {
            return ':Event:Application:detail';
        }
    }

    /**
     * @param AbstractModel|ModelEventParticipant $model
     */
    protected function prepareParams(AbstractModel $model): array
    {
        if ($model->getEvent()->isTeamEvent()) {
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
