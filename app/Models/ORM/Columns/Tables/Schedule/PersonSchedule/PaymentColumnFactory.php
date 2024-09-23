<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Schedule\PersonSchedule;

use FKSDB\Models\ORM\Columns\Types\AbstractColumnFactory;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\Model\Model;
use Nette\InvalidStateException;
use Nette\Utils\Html;

/**
 * @phpstan-extends AbstractColumnFactory<PersonScheduleModel>
 */
class PaymentColumnFactory extends AbstractColumnFactory
{
    /**
     * @param PersonScheduleModel $model
     * @throws CannotAccessModelException
     * @throws \Exception
     */
    protected function createHtmlValue(Model $model): Html
    {
        if (!count($model->schedule_item->getPrice()->getPrices())) {
            return Html::el('span')
                ->addAttributes(['class' => 'badge bg-success'])
                ->addText(_('For free'));
        }
        if (!$model->schedule_item->payable) {
            return Html::el('span')
                ->addAttributes(['class' => 'badge bg-info'])
                ->addText(_('Onsite payment'));
        }
        $container = Html::el('');
        if ($model->payment_deadline) {
            $container->addHtml(
                Html::el('span')
                ->addAttributes(['class' => 'badge bg-danger'])
                    ->addText(sprintf(_('Payment deadline %s'), $model->payment_deadline->format(_('__date_time'))))
            );
        }
        if ($model->getPayment()) {
            $container->addHtml($model->getPayment()->state->badge());
        } else {
            $container->addHtml(Html::el('span')->addAttributes(['class' => 'badge bg-danger'])->addText(_('Payment not found')));
        }
        return $container;
    }

    protected function renderNullModel(): Html
    {
        throw new InvalidStateException();
    }
}
