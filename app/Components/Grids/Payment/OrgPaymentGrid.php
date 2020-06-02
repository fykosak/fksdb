<?php

namespace FKSDB\Components\Grids\Payment;

use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;

/**
 * Class OrgPaymentGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class OrgPaymentGrid extends PaymentGrid {

    private ModelEvent $event;

    /**
     * OrgPaymentGrid constructor.
     * @param ModelEvent $event
     * @param Container $container
     */
    public function __construct(ModelEvent $event, Container $container) {
        parent::__construct($container);
        $this->event = $event;
    }

    protected function getData(): IDataSource {
        $schools = $this->servicePayment->getTable()->where('event_id', $this->event->event_id);
        return new NDataSource($schools);
    }

    /**
     * @param Presenter $presenter
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     * @throws InvalidLinkException
     * @throws NotImplementedException
     * @throws BadTypeException
     */
    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);


        $this->addColumns([
            'payment.payment_uid',
            'person.full_name',
            'event.event',
            'payment.price',
            'payment.state',
            'payment.variable_symbol',
        ]);
        $this->addLink('payment.detail', false);
        $this->paginate = false;
        $this->addCSVDownloadButton();
    }
}
