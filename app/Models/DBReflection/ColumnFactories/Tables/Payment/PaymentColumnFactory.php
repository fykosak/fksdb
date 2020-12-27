<?php

namespace FKSDB\Models\DBReflection\ColumnFactories\Tables\Payment;

use FKSDB\Models\DBReflection\ColumnFactories\AbstractColumnException;
use FKSDB\Models\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\Models\DBReflection\FieldLevelPermission;
use FKSDB\Models\DBReflection\DBReflectionFactory;
use FKSDB\Models\DBReflection\MetaDataFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class Payment
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PaymentColumnFactory extends DefaultColumnFactory {

    private DBReflectionFactory $reflectionFactory;

    public function __construct(DBReflectionFactory $reflectionFactory, MetaDataFactory $metaDataFactory) {
        parent::__construct($metaDataFactory);
        $this->reflectionFactory = $reflectionFactory;
    }

    /**
     * @param mixed ...$args
     * @return BaseControl
     * @throws AbstractColumnException
     */
    protected function createFormControl(...$args): BaseControl {
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

    protected function renderNullModel(): Html {
        return Html::el('span')->addAttributes(['class' => 'badge badge-danger'])->addText(_('Payment not found'));
    }
}
