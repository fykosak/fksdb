<?php

namespace FKSDB\Components\Grids\Payment;

use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelPerson;
use NiftyGrid\DataSource\NDataSource;

/**
 *
 * @author Mišo <miso@fykoscz>
 */
class OrgPaymentGrid extends PaymentGrid {
    /**
     * @var ModelEvent
     */
    private $event;

    public function __construct(\ServicePayment $servicePayment, ModelEvent $event) {
        parent::__construct($servicePayment);
        $this->event = $event;
    }

    /**
     * @param $presenter
     * @throws \NiftyGrid\DuplicateButtonException
     * @throws \NiftyGrid\DuplicateColumnException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $schools = $this->servicePayment->getTable()->where('event_id', $this->event->event_id);

        $dataSource = new NDataSource($schools);
        $this->setDataSource($dataSource);

        $this->addColumnPaymentId();

        $this->addColumn('person_name', _('Person'))->setRenderer(function ($row) {
            return ModelPerson::createFromTableRow($row->person)->getFullName();
        });

        $this->addColumn('person_email', _('e-mail'))->setRenderer(function ($row) {
            return ModelPerson::createFromTableRow($row->person)->getInfo()->email;
        });

        $this->addColumnPrice();

        $this->addColumnsSymbols();

        $this->addColumnState();

        $this->addButtonDetail();
    }
}
