<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Contestant;

use FKSDB\Components\Badges\ContestCategoryBadge;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use Fykosak\NetteORM\Model;
use Nette\Utils\Html;

class ContestCategoryColumnFactory extends ColumnFactory
{
    /**
     * @throws BadTypeException
     */
    protected function createHtmlValue(Model $model): Html
    {
        return ContestCategoryBadge::getHtml($model);
    }

    protected function resolveModel(Model $modelSingle): ?Model
    {
        $contestant = parent::resolveModel($modelSingle);
        return $contestant->contest_category;
    }
}
