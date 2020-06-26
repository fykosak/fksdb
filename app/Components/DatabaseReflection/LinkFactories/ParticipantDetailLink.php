<?php

namespace FKSDB\Components\DatabaseReflection\LinkFactories;

use FKSDB\ORM\AbstractModelSingle;
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
        try {
            $model->getFyziklaniTeam();
            return ':Event:TeamApplication:detail';
        } catch (BadRequestException$exception) {
            return ':Event:Application:detail';
        }
    }

    /**
     * @param AbstractModelSingle|ModelEventParticipant $model
     * @return array
     */
    public function prepareParams(AbstractModelSingle $model): array {
        try {
            $team = $model->getFyziklaniTeam();
            return [
                'eventId' => $model->event_id,
                'id' => $team->e_fyziklani_team_id,
            ];
        } catch (BadRequestException$exception) {
            return [
                'eventId' => $model->event_id,
                'id' => $model->event_participant_id,
            ];
        }
    }

    protected function getModelClassName(): string {
        return ModelEventParticipant::class;
    }
}
