<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables;

use FKSDB\Models\ORM\Columns\Types\AbstractColumnFactory;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\UI\FlagBadge;
use Fykosak\NetteORM\Model;
use Nette\Utils\Html;

/**
 * @phpstan-extends AbstractColumnFactory<SchoolModel,never>
 */
class SchoolColumnFactory extends AbstractColumnFactory
{
    /**
     * @param SchoolModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        return Html::el('span')
            ->addText($model->name_abbrev)
            ->addHtml(FlagBadge::getHtml($model->address->country));
    }
}
