<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Payment;

use FKSDB\Components\Grids\RelatedGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use Nette\DI\Container;

class EventPaymentGrid extends RelatedGrid
{

    public function __construct(EventModel $event, Container $container)
    {
        parent::__construct($container, $event, 'payment');
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        parent::configure();
        $this->addColumns([
            'payment.payment_uid',
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
