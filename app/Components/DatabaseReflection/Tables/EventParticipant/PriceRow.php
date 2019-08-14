<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\Controls\Helpers\Badges\NotSetBadge;
use FKSDB\Components\DatabaseReflection\ValuePrinters\PricePrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelEventParticipant;
use Nette\Utils\Html;
use function is_null;

/**
 * Class PriceRow
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class PriceRow extends AbstractParticipantRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Price');
    }

    /**
     * @param AbstractModelSingle|ModelEventParticipant $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        if (is_null($model->price)) {
            return NotSetBadge::getHtml();
        }
        return (new PricePrinter)($model->getPrice());
    }
}
