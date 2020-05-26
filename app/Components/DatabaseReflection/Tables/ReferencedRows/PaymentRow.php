<?php

namespace FKSDB\Components\DatabaseReflection\ReferencedRows;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\Payment\StateRow;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\IPaymentReferencedModel;
use Nette\Application\BadRequestException;
use Nette\Utils\Html;

/**
 * Class PaymentRow
 * *
 */
class PaymentRow extends AbstractRow {

    /**
     * @inheritDoc
     * @throws BadRequestException
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        if (!$model instanceof IPaymentReferencedModel) {
            throw new BadTypeException(IPaymentReferencedModel::class, $model);
        }
        $payment = $model->getPayment();
        if (is_null($payment)) {
            return Html::el('span')->addAttributes(['class' => 'badge badge-danger'])->addText(_('No payment found'));
        }
        return Html::el('span')
            ->addAttributes(['class' => StateRow::getUIClass($payment)])
            ->addText('#' . $payment->getPaymentId() . ' ' . StateRow::getStateLabel($payment));

    }

    /**
     * @inheritDoc
     */
    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string {
        return _('Payment');
    }
}
