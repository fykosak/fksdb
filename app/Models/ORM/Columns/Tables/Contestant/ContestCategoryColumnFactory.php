<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Contestant;

use FKSDB\Models\ORM\Columns\Types\AbstractColumnFactory;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\UI\ContestCategoryBadge;
use FKSDB\Models\UI\NotSetBadge;
use Fykosak\NetteORM\Model;
use Nette\Utils\Html;

/**
 * @phpstan-extends AbstractColumnFactory<ContestantModel,never>
 */
class ContestCategoryColumnFactory extends AbstractColumnFactory
{
    protected function createHtmlValue(Model $model): Html
    {
        if (!isset($model->contest_category)) {
            return NotSetBadge::getHtml();
        }
        return ContestCategoryBadge::getHtml($model->contest_category, $this->translator);
    }
}
