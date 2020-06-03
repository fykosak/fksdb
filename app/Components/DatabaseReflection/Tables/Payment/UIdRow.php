<?php

namespace FKSDB\Components\DatabaseReflection\Payment;

use FKSDB\Components\DatabaseReflection\AbstractRowException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPayment;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class IdRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class UIdRow extends AbstractPaymentRow {

    public function getTitle(): string {
        return _('Payment UId');
    }

    /**
     * @param mixed ...$args
     * @return BaseControl
     * @throws AbstractRowException
     */
    public function createField(...$args): BaseControl {
        throw new AbstractRowException();
    }

    /**
     * @param AbstractModelSingle|ModelPayment $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return Html::el('span')->addText('#' . $model->getPaymentId());
    }
}
