<?php

namespace FKSDB\Components\Grids\Payment;

use FKSDB\Components\Grids\EntityGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelPayment;
use FKSDB\Models\ORM\Services\ServicePayment;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\IPresenter;
use Nette\DI\Container;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;

class EventPaymentGrid extends EntityGrid {

    public function __construct(ModelEvent $event, Container $container) {
        parent::__construct($container, ServicePayment::class, [
            'payment.payment_uid',
            'person.full_name',
            'payment.price',
            'payment.state',
            'payment.variable_symbol',
        ], [
            'event_id' => $event->event_id,
        ]);
    }

    /**
     * @param IPresenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     * @throws InvalidLinkException
     */
    protected function configure(IPresenter $presenter): void {
        parent::configure($presenter);

        $this->addLink('payment.detail', false);
        $this->paginate = false;
        $this->addCSVDownloadButton();
    }

    protected function getModelClassName(): string {
        return ModelPayment::class;
    }
}
