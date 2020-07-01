<?php

namespace FKSDB\Components\DatabaseReflection\LinkFactories;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventParticipant;
use Nette\Application\BadRequestException;

/**
 * Class ParticipantDetailLink
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ParticipantDetailLink extends AbstractLink {

    public function getText(): string {
        return _('Detail');
    }

    /**
     * @param ModelEventParticipant|AbstractModelSingle $model
     * @return string
     */
    public function getDestination(AbstractModelSingle $model): string {
        if ($model->getEvent()->isTeamEvent()) {
            return ':Event:TeamApplication:detail';
        } else {
            return ':Event:Application:detail';
        }
    }

    /**
     * @param AbstractModelSingle|ModelEventParticipant $model
     * @return array
     * @throws BadRequestException
     */
    public function prepareParams(AbstractModelSingle $model): array {
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
