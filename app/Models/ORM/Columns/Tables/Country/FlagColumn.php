<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Country;

use FKSDB\Models\ORM\Columns\Types\AbstractColumnFactory;
use FKSDB\Models\ORM\Models\CountryModel;
use FKSDB\Models\UI\FlagBadge;
use Fykosak\NetteORM\Model\Model;
use Nette\Utils\Html;

/**
 * @phpstan-extends AbstractColumnFactory<CountryModel>
 */
class FlagColumn extends AbstractColumnFactory
{
    protected function createHtmlValue(Model $model): Html
    {
        return FlagBadge::getHtml($model);
    }
}
