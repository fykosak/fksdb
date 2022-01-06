<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Contest;

use FKSDB\Components\Badges\ContestBadge;
use FKSDB\Models\Exceptions\ContestNotFoundException;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\ModelContest;
use Nette\Utils\Html;

class ContestColumnFactory extends ColumnFactory {

    /**
     * @param AbstractModel|ModelContest $model
     * @throws ContestNotFoundException
     */
    protected function createHtmlValue(AbstractModel $model): Html {
        return ContestBadge::getHtml($model);
    }
}
