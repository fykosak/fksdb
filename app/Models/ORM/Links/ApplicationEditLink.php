<?php

namespace FKSDB\Models\ORM\Links;

use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use Nette\Application\BadRequestException;

class ApplicationEditLink extends LinkFactory {

    public function getText(): string {
        return _('Edit');
    }

    /**
     * @param ModelEventParticipant|AbstractModel $model
     * @return string
     */
    protected function getDestination(AbstractModel $model): string {
        return ':Public:Application:default';
    }

    /**
     * @param AbstractModel|ModelEventParticipant $model
     * @return array
     * @throws BadRequestException
     */
    protected function prepareParams(AbstractModel $model): array {
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
