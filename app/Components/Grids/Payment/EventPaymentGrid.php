<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Payment;

use FKSDB\Components\Grids\RelatedGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;

class EventPaymentGrid extends RelatedGrid
{

    public function __construct(EventModel $event, Container $container)
    {
        parent::__construct($container, $event, 'payment');
    }

    /**
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     * @throws InvalidLinkException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);
        $this->addColumns([
            'payment.payment_uid',
            'person.full_name',
            'payment.price',
            'payment.state',
            'payment.variable_symbol',
        ]);

        $this->addLink('payment.detail', false);
        $this->paginate = false;
        $this->addCSVDownloadButton();
    }
}
