<?php

namespace FKSDB\Components\Grids\Payment;

use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;

/**
 * Class OrgPaymentGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class OrgPaymentGrid extends PaymentGrid {
    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * OrgPaymentGrid constructor.
     * @param ModelEvent $event
     * @param Container $container
     */
    public function __construct(ModelEvent $event, Container $container) {
        parent::__construct($container);
        $this->event = $event;
    }

    /**
     * @param $presenter
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     * @throws InvalidLinkException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $schools = $this->servicePayment->getTable()->where('event_id', $this->event->event_id);

        $dataSource = new NDataSource($schools);
        $this->setDataSource($dataSource);

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
