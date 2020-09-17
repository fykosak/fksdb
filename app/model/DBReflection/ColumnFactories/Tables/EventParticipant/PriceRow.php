<?php

namespace FKSDB\DBReflection\ColumnFactories\EventParticipant;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\DBReflection\ColumnFactories\DefaultColumnFactory;
use FKSDB\ValuePrinters\PricePrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelEventParticipant;
use Nette\Utils\Html;

/**
 * Class PriceRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PriceRow extends DefaultColumnFactory {
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
