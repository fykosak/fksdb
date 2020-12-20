<?php

namespace FKSDB\Models\ORM\Links;

use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use Nette\Application\BadRequestException;

/**
 * Class ParticipantDetailLink
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ApplicationEditLink extends AbstractLink {

    public function getText(): string {
        return _('Edit');
    }

    /**
     * @param ModelEventParticipant|AbstractModelSingle $model
     * @return string
     */
    protected function getDestination(AbstractModelSingle $model): string {
        return ':Public:Application:default';
    }

    /**
     * @param AbstractModelSingle|ModelEventParticipant $model
     * @return array
     * @throws BadRequestException
     */
    protected function prepareParams(AbstractModelSingle $model): array {
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
