<?php

declare(strict_types=1);

namespace FKSDB\Components\Payments;

use FKSDB\Components\Grids\Components\BaseList;
use FKSDB\Components\Grids\Components\Button\Button;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @phpstan-extends BaseList<PaymentModel,array{
 *     state?:string,
 *     vs?:string,
 * }>
 */
final class MyPaymentsList extends BaseList
{
    use PaymentListTrait;

    private PersonModel $person;

    public function __construct(Container $container, PersonModel $person)
    {
        parent::__construct($container, 1024);
        $this->person = $person;
    }

    /**
     * @phpstan-return TypedGroupedSelection<PaymentModel>
     */
    protected function getModels(): TypedGroupedSelection
    {
        return $this->person->getPayments();
    }

    protected function configure(): void
    {
        $this->paginate = true;
        $this->filtered = false;
        $this->counter = true;
        $this->mode = self::ModePanel;
        $this->traitConfigure();
        $this->addButton(
            new Button(
                $this->container,
                $this->getPresenter(),
                new Title(null, _('button.payment.detail')),
                fn(PaymentModel $model): array => [
                    ':Shop:Schedule:detail',
                    ['id' => $model->payment_id, 'eventId' => $model->getScheduleEvent()->event_id],
                ],
                null,
                fn(PaymentModel $model): bool => (bool)$model->getScheduleEvent()
            ),
            'detail'
        );
    }
}
