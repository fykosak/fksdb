<?php

namespace FKSDB\Components\DatabaseReflection\ReferencedRows;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPayment;
use Nette\Utils\Html;

/**
 * Class PaymentRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PaymentRow extends AbstractRow {
    /** @var TableReflectionFactory */
    private $reflectionFactory;

    /**
     * PaymentRow constructor.
     * @param TableReflectionFactory $reflectionFactory
     */
    public function __construct(TableReflectionFactory $reflectionFactory) {
        $this->reflectionFactory = $reflectionFactory;
    }

    /**
     * @param AbstractModelSingle|ModelPayment $model
     * @return Html
     * @throws \Exception
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        $factory = $this->reflectionFactory->loadRowFactory('payment.state');
        $html = $factory->createHtmlValue($model);
        $text = $html->getText();
        $html->setText('#' . $model->getPaymentId() . ' - ' . $text);
        return $html;
    }

    public function getPermissionsValue(): int {
        return self::PERMISSION_USE_GLOBAL_ACL;
    }

    protected function nullModelHtmlValue(): Html {
        return Html::el('span')->addAttributes(['class' => 'badge badge-danger'])->addText(_('Payment not found'));
    }

    public function getTitle(): string {
        return _('Payment');
    }
}
