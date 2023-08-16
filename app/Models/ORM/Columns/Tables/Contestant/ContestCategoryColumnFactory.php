<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Contestant;

use FKSDB\Components\Badges\ContestCategoryBadge;
use FKSDB\Components\Badges\NotSetBadge;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\ContestantModel;
use Fykosak\NetteORM\Model;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<ContestantModel,never>
 */
class ContestCategoryColumnFactory extends ColumnFactory
{
    /**
     * @throws BadTypeException
     */
    protected function createHtmlValue(Model $model): Html
    {
        if (!isset($model->contest_category)) {
            return NotSetBadge::getHtml();
        }
        return ContestCategoryBadge::getHtml($model->contest_category);
    }
}
