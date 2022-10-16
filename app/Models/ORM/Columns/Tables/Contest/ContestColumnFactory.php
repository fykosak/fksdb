<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Contest;

use FKSDB\Components\Badges\ContestBadge;
use FKSDB\Models\Exceptions\ContestNotFoundException;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\ContestModel;
use Nette\Utils\Html;

class ContestColumnFactory extends ColumnFactory
{
    /**
     * @param ContestModel $model
     * @throws ContestNotFoundException
     */
    protected function createHtmlValue(Model $model): Html
    {
        return ContestBadge::getHtml($model);
    }
}
