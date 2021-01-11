<?php

namespace FKSDB\Models\ORM\Columns\ColumnFactories\Tables\EventParticipant;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\Models\ORM\Columns\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\Models\ValuePrinters\StringPrinter;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use Nette\Application\BadRequestException;
use Nette\Utils\Html;

/**
 * Class TeamRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TeamColumnFactory extends DefaultColumnFactory {

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
