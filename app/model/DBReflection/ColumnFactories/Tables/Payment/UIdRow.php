<?php

namespace FKSDB\DBReflection\ColumnFactories\Payment;

use FKSDB\DBReflection\ColumnFactories\AbstractColumnException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPayment;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class IdRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class UIdRow extends AbstractPaymentFactory {

    public function getTitle(): string {
        return _('Payment UId');
    }

    public function createField(...$args): BaseControl {
        throw new AbstractColumnException();
    }

    /**
     * @param AbstractModelSingle|ModelPayment $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return Html::el('span')->addText('#' . $model->getPaymentId());
    }
}
