<?php

namespace FKSDB\Components\DatabaseReflection\Payment;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\AbstractRowException;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class Payment
 * @author Michal Červeňák <miso@fykos.cz>
 */
class Payment extends AbstractRow {
    /**
     * @var TableReflectionFactory
     */
    private $reflectionFactory;

    /**
     * PaymentRow constructor.
     * @param TableReflectionFactory $reflectionFactory
     */
    public function __construct(TableReflectionFactory $reflectionFactory) {
        $this->reflectionFactory = $reflectionFactory;
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
     * @param AbstractModelSingle $model
     * @return Html
     * @throws BadTypeException
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

    protected function createNullHtmlValue(): Html {
        return Html::el('span')->addAttributes(['class' => 'badge badge-danger'])->addText(_('Payment not found'));
    }

    public function getTitle(): string {
        return _('Payment');
    }
}
