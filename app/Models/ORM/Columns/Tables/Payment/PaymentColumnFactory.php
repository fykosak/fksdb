<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Payment;

use FKSDB\Models\ORM\Columns\Types\AbstractColumnFactory;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\Warehouse\WarehouseItemVariantModel;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\Model\Model;
use Nette\Utils\Html;

/**
 * @phpstan-extends AbstractColumnFactory<PaymentModel|WarehouseItemVariantModel>
 */
class PaymentColumnFactory extends AbstractColumnFactory
{
    /**
     * @param PaymentModel $model
     * @throws CannotAccessModelException
     */
    protected function createHtmlValue(Model $model): Html
    {
        $html = $model->state->badge();
        $text = $html->getText();
        $html->setText(sprintf('#%d %s', $model->payment_id, $text));
        return $html;
    }

    protected function renderNullModel(): Html
    {
        return Html::el('span')->addAttributes(['class' => 'badge bg-danger'])->addText(_('Payment not found'));
    }
}
