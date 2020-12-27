<?php

namespace FKSDB\Models\ORM\Columns\Tables\EventParticipant;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ValuePrinters\PricePrinter;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use Nette\Utils\Html;

/**
 * Class PriceRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PriceRow extends ColumnFactory {
    /**
     * @param AbstractModelSingle|ModelEventParticipant $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        if (\is_null($model->price)) {
            return NotSetBadge::getHtml();
        }
        return (new PricePrinter())($model->getPrice());
    }
}
