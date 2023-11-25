<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Payment;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PaymentModel;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Nette\DI\Container;

/**
 * @phpstan-extends BaseGrid<PaymentModel,array{}>
 */
final class EventPaymentGrid extends BaseGrid
{
    private EventModel $event;

    public function __construct(EventModel $event, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    /**
     * @phpstan-return TypedGroupedSelection<PaymentModel>
     */
    protected function getModels(): TypedGroupedSelection
    {
        return $this->event->getPayments();
    }

    /**
     * @throws BadTypeException
     */
    protected function configure(): void
    {
        $this->paginate = false;
        $this->counter = true;
        $this->filtered = false;
        $this->addSimpleReferencedColumns([
            '@payment.payment_id',
            '@person.full_name',
            '@payment.price',
            '@payment.state',
            '@payment.variable_symbol',
        ]);

        $this->addLink('payment.detail');
    }
}
