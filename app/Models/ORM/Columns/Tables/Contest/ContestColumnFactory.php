<?php

namespace FKSDB\Models\ORM\Columns\Tables\Contest;

use FKSDB\Components\Controls\Badges\ContestBadge;
use FKSDB\Models\Exceptions\ContestNotFoundException;
use FKSDB\Models\ORM\Columns\Types\DefaultColumnFactory;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\ModelContest;
use Nette\Utils\Html;

/**
 * Class ContestRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ContestColumnFactory extends DefaultColumnFactory {

    /**
     * @param AbstractModelSingle|ModelContest $model
     * @return Html
     * @throws ContestNotFoundException
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return ContestBadge::getHtml($model);
    }
}
