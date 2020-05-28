<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\Components\DatabaseReflection\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelEventParticipant;
use Nette\Application\BadRequestException;
use Nette\Utils\Html;

/**
 * Class TeamRow
 * *
 */
class TeamRow extends AbstractParticipantRow {

    /**
     * @param ModelEventParticipant $model
     * @inheritDoc
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        try {
            $team = $model->getFyziklaniTeam();
            return (new StringPrinter())($team->name);
        } catch (BadRequestException $exception) {
            return NotSetBadge::getHtml();
        }
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string {
        return _('Team');
    }
}
