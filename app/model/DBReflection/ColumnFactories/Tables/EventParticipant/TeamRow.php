<?php

namespace FKSDB\DBReflection\ColumnFactories\EventParticipant;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\ValuePrinters\StringPrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelEventParticipant;
use Nette\Application\BadRequestException;
use Nette\Utils\Html;

/**
 * Class TeamRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TeamRow extends AbstractParticipantRow {

    /**
     * @param ModelEventParticipant|AbstractModelSingle $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        try {
            $team = $model->getFyziklaniTeam();
            return (new StringPrinter())($team->name);
        } catch (BadRequestException $exception) {
            return NotSetBadge::getHtml();
        }
    }

    public function getTitle(): string {
        return _('Team');
    }
}
