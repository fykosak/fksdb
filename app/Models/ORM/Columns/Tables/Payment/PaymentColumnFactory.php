<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Payment;

use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use FKSDB\Models\ORM\Columns\AbstractColumnException;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\ORMFactory;
use FKSDB\Models\ORM\MetaDataFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\NetteORM\Model;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

class PaymentColumnFactory extends ColumnFactory
{
    private ORMFactory $reflectionFactory;

    public function __construct(ORMFactory $reflectionFactory, MetaDataFactory $metaDataFactory)
    {
        parent::__construct($metaDataFactory);
        $this->reflectionFactory = $reflectionFactory;
    }

    /**
     * @throws AbstractColumnException
     */
    protected function createFormControl(...$args): BaseControl
    {
        throw new AbstractColumnException();
    }

    /**
     * @throws \Exception
     */
    protected function prerenderOriginalModel(Model $originalModel): ?Html
    {
        if ($originalModel instanceof PersonScheduleModel && !$originalModel->schedule_item->isPayable()) {
            return Html::el('span')
                ->addAttributes(['class' => 'badge bg-info'])
                ->addText(_('Not payable'));
        }
        return null;
    }

    /**
     * @param PaymentModel $model
     * @throws BadTypeException
     * @throws CannotAccessModelException
     * @throws \ReflectionException
     */
    protected function createHtmlValue(Model $model): Html
    {
        $factory = $this->reflectionFactory->loadColumnFactory(...explode('.', 'payment.state'));
        $html = $factory->render($model, FieldLevelPermission::ALLOW_FULL);
        $text = $html->getText();
        $html->setText('#' . $model->getPaymentId() . ' - ' . $text);
        return $html;
    }

    protected function renderNullModel(): Html
    {
        return Html::el('span')->addAttributes(['class' => 'badge bg-danger'])->addText(_('Payment not found'));
    }
}
