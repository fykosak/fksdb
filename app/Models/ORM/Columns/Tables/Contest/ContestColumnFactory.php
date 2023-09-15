<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Contest;

use FKSDB\Models\ORM\Columns\Types\AbstractColumnFactory;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\UI\ContestBadge;
use Fykosak\NetteORM\Model;
use Nette\Utils\Html;

/**
 * @phpstan-extends AbstractColumnFactory<ContestModel,never>
 */
class ContestColumnFactory extends AbstractColumnFactory
{

    /**
     * @param ContestModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        return ContestBadge::getHtml($model);
    }
}
