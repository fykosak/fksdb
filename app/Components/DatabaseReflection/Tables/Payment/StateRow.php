<?php

namespace FKSDB\Components\DatabaseReflection\Payment;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPayment;
use Nette\Utils\Html;

/**
 * Class StateRow
 * *
 */
class StateRow extends AbstractPaymentRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('State');
    }

    /**
     * @param AbstractModelSingle $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return Html::el('span')->addAttributes(['class' => self::getUIClass($model)])->addText(self::getStateLabel($model));
    }

    /**
     *
     * @param AbstractModelSingle|ModelPayment $model
     * @return string
     */
    public static function getUIClass(AbstractModelSingle $model): string {
        $class = 'badge ';
        switch ($model->state) {
            case ModelPayment::STATE_WAITING:
                $class .= 'badge-warning';
                break;
            case ModelPayment::STATE_CANCELED:
                $class .= 'badge-secondary';
                break;
            case ModelPayment::STATE_RECEIVED:
                $class .= 'badge-success';
                break;
            case ModelPayment::STATE_NEW:
                $class .= 'badge-primary';
                break;
            default:
                $class .= 'badge-light';
        }
        return $class;
    }

    /**
     * @param ModelPayment|AbstractModelSingle $model
     * @return string
     */
    public static function getStateLabel(AbstractModelSingle $model) {
        switch ($model->state) {
            case ModelPayment::STATE_NEW:
                return _('New payment');

            case ModelPayment::STATE_WAITING:
                return _('Waiting for paying');

            case ModelPayment::STATE_CANCELED:
                return _('Payment canceled');

            case ModelPayment::STATE_RECEIVED:
                return _('Payment received');
            default:
                return $model->state;
        }
    }
}
