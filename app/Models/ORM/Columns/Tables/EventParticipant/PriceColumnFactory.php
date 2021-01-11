<?php

namespace FKSDB\Models\ORM\Columns\ColumnFactories\Tables\EventParticipant;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\Models\ORM\Columns\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\Models\ValuePrinters\PricePrinter;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use Nette\Utils\Html;

/**
 * Class PriceRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PriceColumnFactory extends DefaultColumnFactory {
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
