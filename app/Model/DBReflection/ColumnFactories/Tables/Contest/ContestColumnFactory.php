<?php

namespace FKSDB\Model\DBReflection\ColumnFactories\Tables\Contest;

use FKSDB\Components\Controls\Badges\ContestBadge;
use FKSDB\Model\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\Model\ORM\Models\AbstractModelSingle;
use FKSDB\Model\ORM\Models\ModelContest;
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
