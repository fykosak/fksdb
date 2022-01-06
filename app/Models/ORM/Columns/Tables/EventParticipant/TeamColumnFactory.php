<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\EventParticipant;

use FKSDB\Components\Badges\NotSetBadge;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ValuePrinters\StringPrinter;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use Nette\Utils\Html;

class TeamColumnFactory extends ColumnFactory
{

    /**
     * @param ModelEventParticipant|AbstractModel $model
     */
    protected function createHtmlValue(AbstractModel $model): Html
    {
        $team = $model->getFyziklaniTeam();
        return $team ? (new StringPrinter())($team->name) : NotSetBadge::getHtml();
    }
}
