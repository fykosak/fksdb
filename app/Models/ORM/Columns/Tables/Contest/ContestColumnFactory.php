<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Contest;

use FKSDB\Components\Badges\ContestBadge;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\ContestModel;
use Fykosak\NetteORM\Model;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<ContestModel,never>
 */
class ContestColumnFactory extends ColumnFactory
{

    /**
     * @param ContestModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        return ContestBadge::getHtml($model);
    }
}
