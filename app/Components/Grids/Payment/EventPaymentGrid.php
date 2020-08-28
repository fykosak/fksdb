<?php

namespace FKSDB\Components\Grids\Payment;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelPayment;
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
class EventPaymentGrid extends BaseGrid {

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
        return new NDataSource($this->event->getPayments());
    }

    /**
     * @param Presenter $presenter
     * @return void
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     * @throws InvalidLinkException
     * @throws BadTypeException
     */
    protected function configure(Presenter $presenter): void {
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

    protected function getModelClassName(): string {
        return ModelPayment::class;
    }
}
