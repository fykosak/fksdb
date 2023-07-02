<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Payment;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use Fykosak\NetteORM\TypedGroupedSelection;
use Nette\DI\Container;

class EventPaymentGrid extends BaseGrid
{
    private EventModel $event;

    public function __construct(EventModel $event, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    protected function getModels(): TypedGroupedSelection
    {
        return $this->event->getPayments();
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->addColumns([
            'payment.payment_id',
            'person.full_name',
            'payment.price',
            'payment.state',
            'payment.variable_symbol',
        ]);

        $this->addORMLink('payment.detail');
        $this->paginate = false;
       // $this->addCSVDownloadButton();
    }
}
