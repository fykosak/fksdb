<?php

namespace FKSDB\Components\DatabaseReflection\ReferencedRows;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnFactory;
use FKSDB\Components\DatabaseReflection\FieldLevelPermission;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Utils\Html;

/**
 * Class PaymentRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PaymentRow extends AbstractColumnFactory {
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

    public function getPermission(): FieldLevelPermission {
        return new FieldLevelPermission(self::PERMISSION_ALLOW_ANYBODY, self::PERMISSION_ALLOW_ANYBODY);
    }

    protected function createNullHtmlValue(): Html {
        return Html::el('span')->addAttributes(['class' => 'badge badge-danger'])->addText(_('Payment not found'));
    }

    public function getTitle(): string {
        return _('Payment');
    }
}
