<?php

namespace FKSDB\DBReflection\ColumnFactories\Payment;

use FKSDB\DBReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\DBReflection\ColumnFactories\AbstractColumnException;
use FKSDB\DBReflection\FieldLevelPermission;
use FKSDB\DBReflection\DBReflectionFactory;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class Payment
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PaymentColumnFactory extends AbstractColumnFactory {

    private DBReflectionFactory $reflectionFactory;

    /**
     * PaymentRow constructor.
     * @param DBReflectionFactory $reflectionFactory
     */
    public function __construct(DBReflectionFactory $reflectionFactory) {
        $this->reflectionFactory = $reflectionFactory;
    }

    /**
     * @param mixed ...$args
     * @return BaseControl
     * @throws AbstractColumnException
     */
    public function createField(...$args): BaseControl {
        throw new AbstractColumnException();
    }

    /**
     * @param AbstractModelSingle $model
     * @return Html
     * @throws BadTypeException
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        $factory = $this->reflectionFactory->loadColumnFactory('payment.state');
        $html = $factory->render($model, FieldLevelPermission::ALLOW_FULL);
        $text = $html->getText();
        $html->setText('#' . $model->getPaymentId() . ' - ' . $text);
        return $html;
    }

    public function getPermission(): FieldLevelPermission {
        return new FieldLevelPermission(self::PERMISSION_ALLOW_ANYBODY, self::PERMISSION_ALLOW_ANYBODY);
    }

    protected function renderNullModel(): Html {
        return Html::el('span')->addAttributes(['class' => 'badge badge-danger'])->addText(_('Payment not found'));
    }

    public function getTitle(): string {
        return _('Payment');
    }
}
