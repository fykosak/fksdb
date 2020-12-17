<?php

namespace FKSDB\DBReflection\ColumnFactories\Tables\EventParticipant;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\ValuePrinters\StringPrinter;
use FKSDB\ORM\Models\AbstractModelSingle;
use FKSDB\ORM\Models\ModelEventParticipant;
use Nette\Application\BadRequestException;
use Nette\Utils\Html;

/**
 * Class TeamRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TeamRow extends DefaultColumnFactory {

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
}
