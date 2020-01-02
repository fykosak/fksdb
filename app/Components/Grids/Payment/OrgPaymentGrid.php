<?php

namespace FKSDB\Components\Grids\Payment;

use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\ServicePayment;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 *
 * @author Mišo <miso@fykos.cz>
 */
class OrgPaymentGrid extends PaymentGrid {
    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * OrgPaymentGrid constructor.
     * @param ServicePayment $servicePayment
     * @param ModelEvent $event
     * @param TableReflectionFactory $tableReflectionFactory
     */
    public function __construct(ServicePayment $servicePayment, ModelEvent $event, TableReflectionFactory $tableReflectionFactory) {
        parent::__construct($servicePayment, $tableReflectionFactory);
        $this->event = $event;
    }

    /**
     * @param $presenter
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $schools = $this->servicePayment->getTable()->where('event_id', $this->event->event_id);

        $dataSource = new NDataSource($schools);
        $this->setDataSource($dataSource);

        $this->addColumns([
            DbNames::TAB_PAYMENT . '.id',
            'referenced.person_name',
            // 'referenced.event_name',
            DbNames::TAB_PAYMENT . '.price',
            DbNames::TAB_PAYMENT . '.state',
            DbNames::TAB_PAYMENT . '.variable_symbol',
        ]);

        $this->addLinkButton($presenter, ':Event:payment:detail', 'detail', 'Detail', false, [
            'id' => 'payment_id',
            'eventId' => 'event_id',
        ]);
        $this->paginate = false;
    }
}
