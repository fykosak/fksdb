<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Payment;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\Types\AbstractColumnFactory;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Warehouse\ItemModel;
use FKSDB\Models\ORM\ReflectionFactory;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\Model;
use Nette\Utils\Html;

/**
 * @phpstan-extends AbstractColumnFactory<PaymentModel|ItemModel,never>
 */
class PaymentColumnFactory extends AbstractColumnFactory
{
    private ReflectionFactory $reflectionFactory;

    public function injectFactory(ReflectionFactory $reflectionFactory): void
    {
        $this->reflectionFactory = $reflectionFactory;
    }

    /**
     * @throws \Exception
     */
    protected function prerenderOriginalModel(Model $originalModel): ?Html
    {
        if ($originalModel instanceof PersonScheduleModel) {
            if (!count($originalModel->schedule_item->getPrice()->getPrices())) {
                return Html::el('span')
                    ->addAttributes(['class' => 'badge bg-success'])
                    ->addText(_('For free'));
            }
            if (!$originalModel->schedule_item->payable) {
                return Html::el('span')
                    ->addAttributes(['class' => 'badge bg-info'])
                    ->addText(_('Payment on the spot'));
            }
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
        $factory = $this->reflectionFactory->loadColumnFactory('payment', 'state');
        $html = $factory->render($model, FieldLevelPermission::ALLOW_FULL);
        $text = $html->getText();
        $html->setText(sprintf('#%d %s', $model->payment_id, $text));
        return $html;
    }

    protected function renderNullModel(): Html
    {
        return Html::el('span')->addAttributes(['class' => 'badge bg-danger'])->addText(_('Payment not found'));
    }
}
