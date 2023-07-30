<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\EventParticipant;

use FKSDB\Components\Badges\NotSetBadge;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ValuePrinters\PricePrinter;
use Fykosak\NetteORM\Model;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<EventParticipantModel>
 */
class PriceColumnFactory extends ColumnFactory
{
    /**
     * @param EventParticipantModel $model
     * @throws \Exception
     */
    protected function createHtmlValue(Model $model): Html
    {
        if (\is_null($model->price)) {
            return NotSetBadge::getHtml();
        }
        return (new PricePrinter())($model->getPrice());
    }
}
