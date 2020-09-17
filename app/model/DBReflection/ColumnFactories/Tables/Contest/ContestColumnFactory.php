<?php

namespace FKSDB\DBReflection\ColumnFactories\Contest;

use FKSDB\Components\Controls\Badges\ContestBadge;
use FKSDB\DBReflection\ColumnFactories\DefaultColumnFactory;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelContest;
use Nette\Utils\Html;

/**
 * Class ContestRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ContestColumnFactory extends DefaultColumnFactory {

    /**
     * @param AbstractModelSingle|ModelContest $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return ContestBadge::getHtml($model);
    }
}
